<?php

declare(strict_types=1);

namespace App\Filament\Resources\AppointmentResource\Pages;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppointment extends EditRecord
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === AppointmentStatus::Cancelled->value) {
            $data['cancelled_at'] = $data['cancelled_at'] ?? now();
        }

        if (($data['status'] ?? null) === AppointmentStatus::Confirmed->value) {
            $data['confirmed_at'] = $data['confirmed_at'] ?? now();
        }

        return $data;
    }
}
