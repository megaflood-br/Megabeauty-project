<?php

declare(strict_types=1);

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    public function mount(): void
    {
        parent::mount();

        $professionalId = request()->query('professional_id');
        $startsAt = request()->query('starts_at');

        if (filled($professionalId) || filled($startsAt)) {
            $this->form->fill([
                'professional_id' => filled($professionalId) ? (int) $professionalId : null,
                'starts_at' => $startsAt,
                'status' => AppointmentStatus::Scheduled->value,
            ]);
        }
    }

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
