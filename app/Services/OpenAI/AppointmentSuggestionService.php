<?php

declare(strict_types=1);

namespace App\Services\OpenAI;

use App\Services\OpenAI\DTOs\ChatMessage;
use App\Services\OpenAI\DTOs\SuggestionContext;
use App\Services\OpenAI\DTOs\SuggestionResult;
use App\Services\OpenAI\Exceptions\OpenAIException;

final class AppointmentSuggestionService
{
    public function __construct(
        private readonly OpenAIClient $client,
    ) {}

    /**
     * Send the client conversation history to OpenAI and return structured
     * appointment suggestions for the current tenant.
     *
     * @param  list<ChatMessage|array{role: string, content: string}>  $conversation
     */
    public function suggest(array $conversation, SuggestionContext $context): SuggestionResult
    {
        $history = $this->normalizeConversation($conversation);

        if ($history === []) {
            throw new \InvalidArgumentException('Conversation history cannot be empty.');
        }

        $messages = [
            new ChatMessage('system', $this->systemPrompt()),
            new ChatMessage('system', $this->contextPrompt($context)),
            ...$history,
        ];

        $response = $this->client->chat($messages);
        $content = $this->extractContent($response);
        $payload = $this->decodeJson($content);

        return SuggestionResult::fromArray($payload, $content);
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

        return $messages;
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
You are an assistant for a multi-tenant beauty and scheduling SaaS (salons, clinics and barbershops).
Your job is to read the WhatsApp/client conversation and propose appointment suggestions.

Rules:
- Never invent services or professionals that are not in the provided catalog.
- Use the tenant timezone for every datetime.
- If information is missing (service, professional, date or time), ask a single follow-up question.
- Prefer the soonest reasonable slot that matches the client request.
- Return ONLY valid JSON matching this schema:
{
  "intent": "schedule|reschedule|cancel|question|unknown",
  "confidence": 0.0,
  "summary": "short Portuguese summary of the request",
  "follow_up_question": "string or null",
  "suggestions": [
    {
      "service_name": "string or null",
      "professional_name": "string or null",
      "starts_at": "ISO-8601 datetime or null",
      "ends_at": "ISO-8601 datetime or null",
      "duration_minutes": 30,
      "confidence": 0.0,
      "summary": "why this slot was chosen",
      "missing_information": ["service", "datetime"]
    }
  ]
}
PROMPT;
    }

    private function contextPrompt(SuggestionContext $context): string
    {
        $encoded = json_encode(
            $context->toArray(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );

        return "Tenant catalog and client context:\n{$encoded}";
    }

    /**
     * @param  array<string, mixed>  $response
     */
    private function extractContent(array $response): string
    {
        $content = data_get($response, 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw OpenAIException::invalidResponse('missing choices.0.message.content.');
        }

        return $content;
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(string $content): array
    {
        $payload = json_decode($content, true);

        if (! is_array($payload)) {
            throw OpenAIException::invalidResponse('content is not valid JSON.');
        }

        return $payload;
    }
}
