<?php

declare(strict_types=1);

namespace App\Services\Agents;

use App\Models\AiAgent;
use App\Services\Agents\DTOs\AgentReply;
use App\Services\OpenAI\DTOs\ChatMessage;
use App\Services\OpenAI\Exceptions\OpenAIException;
use App\Services\OpenAI\OpenAIClient;

final class AgentRuntime
{
    public const MAX_ROUNDS = 4;

    public const HISTORY_LIMIT = 12;

    public function __construct(
        private readonly OpenAIClient $client,
        private readonly AgentPromptBuilder $prompts,
        private readonly AgentToolExecutor $tools,
    ) {}

    /**
     * @param  list<ChatMessage|array{role: string, content: string}>  $conversation
     */
    public function reply(AiAgent $agent, array $conversation): AgentReply
    {
        if (! $agent->is_active) {
            throw new \InvalidArgumentException("O agente [{$agent->name}] está inativo.");
        }

        $messages = [
            ...$this->prompts->systemMessages($agent),
            ...$this->normalizeConversation($conversation),
        ];

        $tools = $this->tools->definitions($agent);
        $traces = [];

        for ($round = 1; $round <= self::MAX_ROUNDS; $round++) {
            $options = [
                'model' => $agent->model ?: config('openai.model'),
                'temperature' => (float) $agent->temperature,
                'max_tokens' => (int) $agent->max_tokens,
                'response_format' => null,
            ];

            if ($tools !== []) {
                $options['tools'] = $tools;
                $options['tool_choice'] = 'auto';
            }

            $response = $this->client->chat($messages, $options);
            $choice = data_get($response, 'choices.0.message');

            if (! is_array($choice)) {
                throw OpenAIException::invalidResponse('missing choices.0.message.');
            }

            $toolCalls = $choice['tool_calls'] ?? null;

            if (is_array($toolCalls) && $toolCalls !== []) {
                $messages[] = [
                    'role' => 'assistant',
                    'content' => $choice['content'] ?? null,
                    'tool_calls' => $toolCalls,
                ];

                foreach ($toolCalls as $call) {
                    $trace = $this->runToolCall($agent, $call);
                    $traces[] = $trace['trace'];
                    $messages[] = $trace['message'];
                }

                continue;
            }

            $content = trim((string) ($choice['content'] ?? ''));

            if ($content === '') {
                throw OpenAIException::invalidResponse('missing assistant content.');
            }

            return new AgentReply($content, $traces, $round);
        }

        throw OpenAIException::invalidResponse('tool loop exceeded the maximum number of rounds.');
    }

    /**
     * @param  list<ChatMessage|array{role: string, content: string}>  $conversation
     * @return list<ChatMessage>
     */
    private function normalizeConversation(array $conversation): array
    {
        $messages = [];

        foreach ($conversation as $item) {
            $message = $item instanceof ChatMessage
                ? $item
                : ChatMessage::fromArray($item);

            if ($message->role === 'system') {
                continue;
            }

            $messages[] = $message;
        }

        if ($messages === []) {
            throw new \InvalidArgumentException('Conversation history cannot be empty.');
        }

        if (count($messages) > self::HISTORY_LIMIT) {
            $messages = array_slice($messages, -self::HISTORY_LIMIT);
        }

        return array_values($messages);
    }

    /**
     * @param  array<string, mixed>  $call
     * @return array{trace: array{name: string, arguments: array<string, mixed>, result_summary: string}, message: array<string, mixed>}
     */
    private function runToolCall(AiAgent $agent, array $call): array
    {
        $id = (string) ($call['id'] ?? '');
        $name = (string) data_get($call, 'function.name', '');
        $rawArguments = (string) data_get($call, 'function.arguments', '{}');

        try {
            $decoded = json_decode($rawArguments, true, 512, JSON_THROW_ON_ERROR);
            $arguments = is_array($decoded) ? $decoded : [];
        } catch (\JsonException) {
            $arguments = [];
            $result = ['erro' => 'Argumentos da ferramenta inválidos.'];
        }

        $result ??= $this->tools->execute($agent, $name, $arguments);

        $encoded = json_encode(
            $result,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        return [
            'trace' => [
                'name' => $name,
                'arguments' => $arguments,
                'result_summary' => $this->summarizeResult($name, $result),
            ],
            'message' => [
                'role' => 'tool',
                'tool_call_id' => $id !== '' ? $id : 'call_unknown',
                'content' => $encoded,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function summarizeResult(string $name, array $result): string
    {
        if (isset($result['erro'])) {
            return (string) $result['erro'];
        }

        return match ($name) {
            AiAgent::TOOL_LOOKUP_PRODUCTS => count($result['produtos'] ?? []).' produto(s) encontrado(s)',
            AiAgent::TOOL_LOOKUP_SERVICES => count($result['servicos'] ?? []).' serviço(s) encontrado(s)',
            AiAgent::TOOL_LOOKUP_PRICE_TABLES => count($result['tabelas'] ?? []).' tabela(s) encontrada(s)',
            AiAgent::TOOL_GET_COMPANY_INFO => 'Dados da empresa carregados',
            default => 'Ferramenta executada',
        };
    }
}
