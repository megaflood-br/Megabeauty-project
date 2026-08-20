<?php

declare(strict_types=1);

namespace App\Services\OpenAI\DTOs;

final readonly class SuggestionResult
{
    /**
     * @param  list<AppointmentSuggestion>  $suggestions
     */
    public function __construct(
        public string $intent,
        public float $confidence,
        public string $summary,
        public array $suggestions,
        public ?string $followUpQuestion = null,
        public ?string $rawContent = null,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload, ?string $rawContent = null): self
    {
        $items = $payload['suggestions'] ?? [];
        $suggestions = [];

        if (is_array($items)) {
            foreach ($items as $item) {
                if (is_array($item)) {
                    $suggestions[] = AppointmentSuggestion::fromArray($item);
                }
            }
        }

        return new self(
            intent: isset($payload['intent']) && is_string($payload['intent'])
                ? $payload['intent']
                : 'unknown',
            confidence: isset($payload['confidence']) && is_numeric($payload['confidence'])
                ? (float) $payload['confidence']
                : 0.0,
            summary: isset($payload['summary']) && is_string($payload['summary'])
                ? $payload['summary']
                : '',
            suggestions: $suggestions,
            followUpQuestion: isset($payload['follow_up_question']) && is_string($payload['follow_up_question'])
                ? $payload['follow_up_question']
                : null,
            rawContent: $rawContent,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'intent' => $this->intent,
            'confidence' => $this->confidence,
            'summary' => $this->summary,
            'follow_up_question' => $this->followUpQuestion,
            'suggestions' => array_map(
                static fn (AppointmentSuggestion $suggestion): array => $suggestion->toArray(),
                $this->suggestions,
            ),
        ];
    }
}
