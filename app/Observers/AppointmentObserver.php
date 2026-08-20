<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\SendWhatsAppNotificationJob;
use App\Models\Appointment;

final class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        SendWhatsAppNotificationJob::dispatch($appointment->getKey());
    }
}
