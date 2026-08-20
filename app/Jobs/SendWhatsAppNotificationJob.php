<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\Evolution\EvolutionApiService;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public readonly int $appointmentId,
    ) {}

    public function handle(EvolutionApiService $evolution, TenantContext $context): void
    {
        $appointment = Appointment::withoutTenant()
            ->with(['client', 'professional', 'service', 'tenant.evolutionApiSetting'])
            ->find($this->appointmentId);

        if ($appointment === null || $appointment->tenant === null) {
            return;
        }

        $context->run($appointment->tenant, function () use ($evolution, $appointment): void {
            $settings = $appointment->tenant?->evolutionApiSetting;

            if ($settings === null || ! $settings->isConfigured() || ! $settings->is_active) {
                Log::info('WhatsApp reminder skipped: Evolution API is not configured.', [
                    'appointment_id' => $appointment->id,
                    'tenant_id' => $appointment->tenant_id,
                ]);

                return;
            }

            if (blank($appointment->client?->phone)) {
                return;
            }

            $evolution->sendAppointmentReminder($appointment);
        });
    }
}
