<?php

declare(strict_types=1);

namespace App\Services\Agents\DTOs;

final readonly class AgentReply
{
    /**
     * @param  list<array{name: string, arguments: array<string, mixed>, result_summary: string}>  $toolTraces
     */
    public function __construct(
        public string $content,
        public array $toolTraces = [],
        public int $rounds = 1,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'tool_traces' => $this->toolTraces,
            'rounds' => $this->rounds,
        ];
    }
}
