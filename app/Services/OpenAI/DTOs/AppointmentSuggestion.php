<?php

declare(strict_types=1);

namespace App\Services\OpenAI\DTOs;

final readonly class AppointmentSuggestion
{
    /**
     * @param  list<string>  $missingInformation
     */
    public function __construct(
        public ?string $serviceName,
        public ?string $professionalName,
        public ?string $startsAt,
        public ?string $endsAt,
        public ?int $durationMinutes,
        public float $confidence,
        public string $summary,
        public array $missingInformation = [],
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $missing = $payload['missing_information'] ?? [];

        if (! is_array($missing)) {
            $missing = [];
        }

        /** @var list<string> $missingInformation */
        $missingInformation = array_values(array_filter(
            $missing,
            static fn (mixed $item): bool => is_string($item) && $item !== '',
        ));

        return new self(
            serviceName: isset($payload['service_name']) && is_string($payload['service_name'])
                ? $payload['service_name']
                : null,
            professionalName: isset($payload['professional_name']) && is_string($payload['professional_name'])
                ? $payload['professional_name']
                : null,
            startsAt: isset($payload['starts_at']) && is_string($payload['starts_at'])
                ? $payload['starts_at']
                : null,
            endsAt: isset($payload['ends_at']) && is_string($payload['ends_at'])
                ? $payload['ends_at']
                : null,
            durationMinutes: isset($payload['duration_minutes']) && is_numeric($payload['duration_minutes'])
                ? (int) $payload['duration_minutes']
                : null,
            confidence: isset($payload['confidence']) && is_numeric($payload['confidence'])
                ? (float) $payload['confidence']
                : 0.0,
            summary: isset($payload['summary']) && is_string($payload['summary'])
                ? $payload['summary']
                : '',
            missingInformation: $missingInformation,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'service_name' => $this->serviceName,
            'professional_name' => $this->professionalName,
            'starts_at' => $this->startsAt,
            'ends_at' => $this->endsAt,
            'duration_minutes' => $this->durationMinutes,
            'confidence' => $this->confidence,
            'summary' => $this->summary,
            'missing_information' => $this->missingInformation,
        ];
    }
}
