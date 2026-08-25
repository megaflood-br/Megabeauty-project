<?php

declare(strict_types=1);

namespace App\Services\OpenAI;

use App\Services\OpenAI\DTOs\ChatMessage;
use App\Services\OpenAI\Exceptions\OpenAIException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;

final class OpenAIClient
{
    public function __construct(
        private readonly HttpFactory $http,
    ) {}

    /**
     * @param  list<ChatMessage|array<string, mixed>>  $messages
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function chat(array $messages, array $options = []): array
    {
        $apiKey = (string) config('openai.api_key');

        if ($apiKey === '') {
            throw OpenAIException::missingApiKey();
        }

        $payload = [
            'model' => $options['model'] ?? config('openai.model'),
            'temperature' => $options['temperature'] ?? config('openai.temperature'),
            'max_tokens' => $options['max_tokens'] ?? config('openai.max_tokens'),
            'messages' => $this->serializeMessages($messages),
        ];

        $hasTools = isset($options['tools']) && is_array($options['tools']) && $options['tools'] !== [];

        $responseFormat = array_key_exists('response_format', $options)
            ? $options['response_format']
            : ($hasTools ? null : ['type' => 'json_object']);

        if (is_array($responseFormat)) {
            $payload['response_format'] = $responseFormat;
        }

        if ($hasTools) {
            $payload['tools'] = $options['tools'];
            $payload['tool_choice'] = $options['tool_choice'] ?? 'auto';
        }

        try {
            $response = $this->http
                ->baseUrl(rtrim((string) config('openai.base_url'), '/'))
                ->withToken($apiKey)
                ->acceptJson()
                ->timeout((int) config('openai.timeout'))
                ->retry(2, 200, throw: false)
                ->post('/chat/completions', $payload);
        } catch (RequestException $exception) {
            $failed = $exception->response;

            throw OpenAIException::requestFailed(
                $failed?->status() ?? 0,
                $failed?->body() ?? $exception->getMessage(),
            );
        }

        if ($response->failed()) {
            throw OpenAIException::requestFailed($response->status(), $response->body());
        }

        return $this->decode($response);
    }

    /**
     * @param  list<ChatMessage|array<string, mixed>>  $messages
     * @return list<array<string, mixed>>
     */
    private function serializeMessages(array $messages): array
    {
        return array_map(
            static function (ChatMessage|array $message): array {
                return $message instanceof ChatMessage ? $message->toArray() : $message;
            },
            $messages,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(Response $response): array
    {
        $decoded = $response->json();

        if (! is_array($decoded)) {
            throw OpenAIException::invalidResponse('payload is not a JSON object.');
        }

        return $decoded;
    }
}
