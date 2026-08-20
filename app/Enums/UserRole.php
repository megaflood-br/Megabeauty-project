<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Receptionist = 'receptionist';
    case Professional = 'professional';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Proprietário',
            self::Admin => 'Administrador',
            self::Receptionist => 'Recepcionista',
            self::Professional => 'Profissional',
        };
    }
}
