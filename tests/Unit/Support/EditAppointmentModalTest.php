<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Models\Appointment;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Tenant;
use App\Support\Agenda\EditAppointmentModal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EditAppointmentModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_mutate_form_data_syncs_primary_item_onto_the_appointment(): void
    {
        $tenant = Tenant::factory()->create();
        $this->actingAsTenant($tenant);

        $professional = Professional::factory()->create(['tenant_id' => $tenant->id]);
        $service = Service::factory()->create([
            'tenant_id' => $tenant->id,
            'duration_minutes' => 90,
            'price' => 120,
        ]);

        $data = EditAppointmentModal::mutateFormData([
            'date' => '2026-08-20',
            'status' => 'confirmed',
            'color' => '#059669',
            'send_reminder' => true,
            'squeeze' => false,
            'repeat_rule' => 'none',
            'notes' => 'Observação',
            'items' => [
                'uuid-1' => [
                    'service_id' => $service->id,
                    'professional_id' => $professional->id,
                    'time' => '09:00',
                    'duration' => 90,
                ],
            ],
        ]);

        $this->assertSame($professional->id, $data['professional_id']);
        $this->assertSame($service->id, $data['service_id']);
        $this->assertSame('2026-08-20 09:00:00', $data['starts_at']);
        $this->assertSame('2026-08-20 10:30:00', $data['ends_at']);
        $this->assertEquals(120.0, (float) $data['price']);
        $this->assertArrayNotHasKey('date', $data);
        $this->assertNotNull($data['confirmed_at']);
    }

    public function test_mutate_record_data_builds_a_single_item_from_the_appointment(): void
    {
        $tenant = Tenant::factory()->create();
        $this->actingAsTenant($tenant);

        $appointment = Appointment::factory()->create([
            'tenant_id' => $tenant->id,
            'starts_at' => now()->setTime(9, 0),
            'ends_at' => now()->setTime(10, 30),
        ]);

        $data = EditAppointmentModal::mutateRecordData($appointment, $appointment->attributesToArray());

        $this->assertSame($appointment->starts_at->toDateString(), $data['date']);
        $this->assertSame('09:00', $data['items'][0]['time']);
        $this->assertSame(90, $data['items'][0]['duration']);
        $this->assertSame($appointment->id, $data['record_id']);
    }

    public function test_time_options_work_when_adding_an_item_without_professional(): void
    {
        $options = EditAppointmentModal::timeOptions(null, '2026-08-20', null);

        $this->assertArrayHasKey('09:00', $options);
        $this->assertSame('09:00', $options['09:00']);
    }
}
