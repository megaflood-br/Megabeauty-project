<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\AgendaSlotInterval;
use App\Models\Tenant;
use Tests\TestCase;

final class AgendaSlotIntervalTest extends TestCase
{
    public function test_exposes_every_supported_interval(): void
    {
        $this->assertSame([
            5 => '5 em 5 minutos',
            10 => '10 em 10 minutos',
            15 => '15 em 15 minutos',
            20 => '20 em 20 minutos',
            30 => '30 em 30 minutos',
            60 => '1 em 1 hora',
        ], AgendaSlotInterval::options());
    }

    public function test_tenant_clamps_and_persists_the_slot_interval(): void
    {
        $tenant = new Tenant(['settings' => []]);

        $this->assertSame(15, $tenant->agendaSlotMinutes());

        $tenant->setAgendaSlotMinutes(5);
        $this->assertSame(5, $tenant->agendaSlotMinutes());

        $tenant->setAgendaSlotMinutes(7);
        $this->assertSame(15, $tenant->agendaSlotMinutes());
    }
}
