<?php

declare(strict_types=1);

namespace App\Enums;

enum AgendaSlotInterval: int
{
    case Five = 5;
    case Ten = 10;
    case Fifteen = 15;
    case Twenty = 20;
    case Thirty = 30;
    case Sixty = 60;

    public function label(): string
    {
        return match ($this) {
            self::Five => '5 em 5 minutos',
            self::Ten => '10 em 10 minutos',
            self::Fifteen => '15 em 15 minutos',
            self::Twenty => '20 em 20 minutos',
            self::Thirty => '30 em 30 minutos',
            self::Sixty => '1 em 1 hora',
        };
    }

    /**
     * @return array<int, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $interval): array => [$interval->value => $interval->label()])
            ->all();
    }

    public static function clamp(int $minutes): int
    {
        return self::tryFrom($minutes)?->value ?? self::Fifteen->value;
    }
}
