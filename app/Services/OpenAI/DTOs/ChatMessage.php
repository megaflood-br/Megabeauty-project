<?php

declare(strict_types=1);

namespace App\Services\OpenAI\DTOs;

final readonly class ChatMessage
{
    public function __construct(
        public string $role,
        public string $content,
    ) {
        if (! in_array($this->role, ['system', 'user', 'assistant'], true)) {
            throw new \InvalidArgumentException("Invalid chat role [{$this->role}].");
        }

        if (trim($this->content) === '') {
            throw new \InvalidArgumentException('Chat message content cannot be empty.');
        }
    }

    /**
     * @param  array{role: string, content: string}  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            role: $payload['role'],
            content: $payload['content'],
        );
    }

    /**
     * @return array{role: string, content: string}
     */
    public function toArray(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }
}
