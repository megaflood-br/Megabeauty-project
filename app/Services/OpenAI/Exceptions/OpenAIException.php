<?php

declare(strict_types=1);

namespace App\Services\OpenAI\Exceptions;

use RuntimeException;

final class OpenAIException extends RuntimeException
{
    public static function missingApiKey(): self
    {
        return new self('OPENAI_API_KEY is not configured.');
    }

    public static function requestFailed(int $status, string $body): self
    {
        return new self("OpenAI request failed with HTTP {$status}: {$body}");
    }

    public static function invalidResponse(string $reason): self
    {
        return new self("OpenAI returned an invalid response: {$reason}");
    }
}
