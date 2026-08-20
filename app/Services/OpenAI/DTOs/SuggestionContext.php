<?php

declare(strict_types=1);

namespace App\Services\OpenAI\DTOs;

final readonly class SuggestionContext
{
    /**
     * @param  list<array<string, mixed>>  $services
     * @param  list<array<string, mixed>>  $professionals
     * @param  array<string, mixed>  $client
     */
    public function __construct(
        public string $tenantName,
        public string $timezone = 'America/Sao_Paulo',
        public array $client = [],
        public array $services = [],
        public array $professionals = [],
        public ?string $now = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'tenant_name' => $this->tenantName,
            'timezone' => $this->timezone,
            'now' => $this->now ?? now($this->timezone)->toIso8601String(),
            'client' => $this->client,
            'services' => $this->services,
            'professionals' => $this->professionals,
        ];
    }
}
