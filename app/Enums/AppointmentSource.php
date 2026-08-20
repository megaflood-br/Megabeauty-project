<?php

declare(strict_types=1);

namespace App\Enums;

enum AppointmentSource: string
{
    case Manual = 'manual';
    case WhatsApp = 'whatsapp';
    case Ai = 'ai';
    case Online = 'online';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::WhatsApp => 'WhatsApp',
            self::Ai => 'IA',
            self::Online => 'Online',
        };
    }
}
