<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\ManageAgendaSettings;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ManageAgendaSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_change_the_agenda_slot_interval(): void
    {
        $tenant = Tenant::factory()->create([
            'subdomain' => 'demo',
            'settings' => ['agenda_slot_minutes' => 15],
        ]);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user);
        $this->actingAsTenant($tenant);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($tenant);

        $this->get('/admin/'.$tenant->subdomain.'/manage-agenda-settings')
            ->assertOk()
            ->assertSee('Intervalo da agenda');

        Livewire::test(ManageAgendaSettings::class)
            ->fillForm([
                'agenda_slot_minutes' => 10,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(10, $tenant->refresh()->agendaSlotMinutes());
    }

    public function test_owner_can_save_five_and_sixty_minute_intervals(): void
    {
        $tenant = Tenant::factory()->create([
            'subdomain' => 'demo',
            'settings' => ['agenda_slot_minutes' => 15],
        ]);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user);
        $this->actingAsTenant($tenant);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($tenant);

        Livewire::test(ManageAgendaSettings::class)
            ->fillForm(['agenda_slot_minutes' => 5])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(5, $tenant->refresh()->agendaSlotMinutes());

        Livewire::test(ManageAgendaSettings::class)
            ->fillForm(['agenda_slot_minutes' => 60])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(60, $tenant->refresh()->agendaSlotMinutes());
    }
}
