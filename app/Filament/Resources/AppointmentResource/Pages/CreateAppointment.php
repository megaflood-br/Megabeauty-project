<?php

declare(strict_types=1);

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['status'] ?? null) === AppointmentStatus::Cancelled->value) {
            $data['cancelled_at'] = now();
        }

        if (($data['status'] ?? null) === AppointmentStatus::Confirmed->value) {
            $data['confirmed_at'] = now();
        }

        return $data;
    }
}
