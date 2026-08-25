<?php

declare(strict_types=1);

namespace App\Enums;

enum AiAgentChannel: string
{
    case Playground = 'playground';
    case Widget = 'widget';
    case WhatsApp = 'whatsapp';
    case Api = 'api';

    public function label(): string
    {
        return match ($this) {
            self::Playground => 'Playground',
            self::Widget => 'Widget',
            self::WhatsApp => 'WhatsApp',
            self::Api => 'API',
        };
    }
}
