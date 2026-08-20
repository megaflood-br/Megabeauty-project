<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class SendWhatsAppNotificationJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_appointment_dispatches_the_whatsapp_job(): void
    {
        Queue::fake();

        $tenant = Tenant::factory()->create();
        $this->actingAsTenant($tenant);

        Appointment::factory()->forTenant($tenant)->create([
            'tenant_id' => $tenant->id,
            'client_id' => Client::factory()->create(['tenant_id' => $tenant->id])->id,
            'professional_id' => Professional::factory()->create(['tenant_id' => $tenant->id])->id,
            'service_id' => Service::factory()->create(['tenant_id' => $tenant->id])->id,
        ]);

        Queue::assertPushed(SendWhatsAppNotificationJob::class);
    }
}
