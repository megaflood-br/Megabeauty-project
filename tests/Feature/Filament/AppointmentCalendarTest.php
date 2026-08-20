<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        Appointment::factory()->create([
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
            ->assertSee('09:00');
    }
}
