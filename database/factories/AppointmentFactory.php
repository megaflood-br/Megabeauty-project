<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $startsAt = now()->addDay()->setTime(14, 0);

        return [
            'tenant_id' => Tenant::factory(),
            'client_id' => Client::factory(),
            'professional_id' => Professional::factory(),
            'service_id' => Service::factory(),
            'starts_at' => $startsAt,
            'ends_at' => (clone $startsAt)->addHour(),
            'status' => AppointmentStatus::Scheduled,
            'source' => AppointmentSource::Manual,
            'price' => fake()->randomFloat(2, 50, 250),
            'notes' => null,
        ];
    }

    public function forTenant(Tenant $tenant): static
    {
        return $this->state(fn (): array => [
            'tenant_id' => $tenant->id,
            'client_id' => Client::factory()->state(['tenant_id' => $tenant->id]),
            'professional_id' => Professional::factory()->state(['tenant_id' => $tenant->id]),
            'service_id' => Service::factory()->state(['tenant_id' => $tenant->id]),
        ]);
    }
}
