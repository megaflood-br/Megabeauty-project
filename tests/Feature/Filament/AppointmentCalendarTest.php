<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\AppointmentStatus;
use App\Filament\Pages\AppointmentCalendar;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AppointmentCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_resource_timeline_renders_professionals_and_appointment_blocks(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'demo']);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);
        $this->actingAsTenant($tenant);

        $ana = Professional::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Ana Souza',
            'color' => '#059669',
        ]);
        $service = Service::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Alongamento de Unha',
        ]);
        $client = Client::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Ana Caroline Torres',
        ]);

        $appointment = Appointment::factory()->create([
            'tenant_id' => $tenant->id,
            'professional_id' => $ana->id,
            'service_id' => $service->id,
            'client_id' => $client->id,
            'starts_at' => now()->setTime(9, 0),
            'ends_at' => now()->setTime(11, 10),
            'status' => AppointmentStatus::Confirmed,
        ]);

        $this->actingAs($user)
            ->get('/admin/'.$tenant->subdomain.'/appointment-calendar')
            ->assertOk()
            ->assertSee('Ana Souza')
            ->assertSee('Ana Caroline Torres')
            ->assertSee('Alongamento de Unha')
            ->assertSee('08:00')
            ->assertSee('08:15')
            ->assertSee('08:30')
            ->assertSee('09:00')
            ->assertSee('15 em 15 minutos')
            ->assertSee("mountAction('editAppointment'", false)
            ->assertDontSee('/appointments/'.$appointment->id.'/edit', false);
    }

    public function test_calendar_uses_the_tenant_slot_interval(): void
    {
        $tenant = Tenant::factory()->create([
            'subdomain' => 'demo',
            'settings' => ['agenda_slot_minutes' => 10],
        ]);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);
        $this->actingAsTenant($tenant);

        Professional::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Ana Souza',
        ]);

        $this->actingAs($user)
            ->get('/admin/'.$tenant->subdomain.'/appointment-calendar')
            ->assertOk()
            ->assertSee('08:00')
            ->assertSee('08:10')
            ->assertSee('08:20')
            ->assertSee('10 em 10 minutos');
    }

    public function test_appointment_block_opens_an_edit_modal_and_saves_changes(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'demo']);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);
        $this->actingAsTenant($tenant);

        $professional = Professional::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Bianca',
        ]);
        $service = Service::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Corte',
        ]);
        $client = Client::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Aline Alves',
        ]);

        $appointment = Appointment::factory()->create([
            'tenant_id' => $tenant->id,
            'professional_id' => $professional->id,
            'service_id' => $service->id,
            'client_id' => $client->id,
            'starts_at' => now()->setTime(9, 0),
            'ends_at' => now()->setTime(10, 0),
            'status' => AppointmentStatus::Confirmed,
            'notes' => null,
        ]);

        $this->actingAs($user);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($tenant);

        Livewire::test(AppointmentCalendar::class)
            ->mountAction('editAppointment', ['record' => $appointment->id])
            ->assertSee('Editando agendamento')
            ->assertSee('Aline Alves')
            ->assertSee('Conversar')
            ->assertSee('Itens do agendamento')
            ->assertSee('Enviar lembrete')
            ->assertSee('Criar comanda')
            ->assertActionDataSet([
                'client_id' => $appointment->client_id,
            ])
            ->setActionData([
                'notes' => 'Atualizado no modal',
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'notes' => 'Atualizado no modal',
        ]);
    }
}
