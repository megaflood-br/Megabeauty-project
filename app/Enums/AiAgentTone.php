<?php

declare(strict_types=1);

namespace App\Enums;

enum AiAgentTone: string
{
    case Formal = 'formal';
    case Cordial = 'cordial';
    case Relaxed = 'relaxed';
    case Consultative = 'consultative';
    case Commercial = 'commercial';

    public function label(): string
    {
        return match ($this) {
            self::Formal => 'Formal',
            self::Cordial => 'Cordial',
            self::Relaxed => 'Descontraído',
            self::Consultative => 'Consultivo',
            self::Commercial => 'Comercial',
        };
    }

    public function promptHint(): string
    {
        return match ($this) {
            self::Formal => 'Fale de forma polida, objetiva e profissional, sem gírias.',
            self::Cordial => 'Fale de forma simpática, acolhedora e educada, como uma recepcionista experiente.',
            self::Relaxed => 'Fale de forma leve e próxima, como no WhatsApp, sem perder clareza.',
            self::Consultative => 'Faça perguntas curtas para entender a necessidade antes de recomendar.',
            self::Commercial => 'Destaque valor, combos e disponibilidade, sem pressão excessiva e sem inventar promoções.',
        };
    }
}
