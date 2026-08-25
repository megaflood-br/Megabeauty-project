<?php

declare(strict_types=1);

namespace App\Enums;

enum AiAgentRole: string
{
    case Receptionist = 'receptionist';
    case Consultant = 'consultant';
    case Sales = 'sales';
    case Secretary = 'secretary';
    case Specialist = 'specialist';

    public function label(): string
    {
        return match ($this) {
            self::Receptionist => 'Recepcionista',
            self::Consultant => 'Consultora de atendimento',
            self::Sales => 'Consultora de vendas',
            self::Secretary => 'Secretária virtual',
            self::Specialist => 'Especialista do salão',
        };
    }

    public function promptHint(): string
    {
        return match ($this) {
            self::Receptionist => 'Você recebe clientes, tira dúvidas rápidas, informa valores e ajuda a agendar.',
            self::Consultant => 'Você consulta necessidades, recomenda serviços e explica benefícios com clareza.',
            self::Sales => 'Você ajuda a escolher produtos e serviços, informa preços e sugere combos sem ser agressiva.',
            self::Secretary => 'Você organiza informações da empresa, horários, políticas e encaminha o que for operacional.',
            self::Specialist => 'Você tira dúvidas técnicas sobre serviços e produtos do catálogo, sem inventar indicações clínicas.',
        };
    }
}
