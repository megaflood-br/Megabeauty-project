<?php

declare(strict_types=1);

namespace App\Enums;

enum AppointmentSource: string
{
    case Manual = 'manual';
    case WhatsApp = 'whatsapp';
    case Ai = 'ai';
    case Online = 'online';
}
