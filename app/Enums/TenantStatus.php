<?php

declare(strict_types=1);

namespace App\Enums;

enum TenantStatus: string
{
    case Trial = 'trial';
    case Active = 'active';
    case Suspended = 'suspended';
    case Cancelled = 'cancelled';

    public function isAccessible(): bool
    {
        return match ($this) {
            self::Trial, self::Active => true,
            self::Suspended, self::Cancelled => false,
        };
    }
}
