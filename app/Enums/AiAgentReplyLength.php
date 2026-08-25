<?php

declare(strict_types=1);

namespace App\Enums;

enum AiAgentReplyLength: string
{
    case Short = 'short';
    case Medium = 'medium';
    case Long = 'long';

    public function label(): string
    {
        return match ($this) {
            self::Short => 'Curta (WhatsApp)',
            self::Medium => 'Média',
            self::Long => 'Detalhada',
        };
    }

    public function promptHint(): string
    {
        return match ($this) {
            self::Short => 'Responda em no máximo 3 frases curtas, no estilo WhatsApp.',
            self::Medium => 'Responda em um ou dois parágrafos curtos, com o essencial.',
            self::Long => 'Pode explicar com mais detalhe, ainda em linguagem simples.',
        };
    }
}
