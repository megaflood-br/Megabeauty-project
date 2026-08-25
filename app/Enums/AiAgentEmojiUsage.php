<?php

declare(strict_types=1);

namespace App\Enums;

enum AiAgentEmojiUsage: string
{
    case None = 'none';
    case Few = 'few';
    case Many = 'many';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Sem emojis',
            self::Few => 'Poucos emojis',
            self::Many => 'Emojis à vontade',
        };
    }
}
