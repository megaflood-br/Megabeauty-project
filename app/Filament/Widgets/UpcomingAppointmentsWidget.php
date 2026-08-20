<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingAppointmentsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Próximos horários';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Appointment::query()
                    ->with(['client', 'professional', 'service'])
                    ->where('starts_at', '>=', now()->startOfDay())
                    ->whereNotIn('status', [AppointmentStatus::Cancelled, AppointmentStatus::NoShow])
                    ->orderBy('starts_at')
                    ->limit(8)
            )
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Horário')
                    ->dateTime('d/m H:i'),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente'),
                Tables\Columns\TextColumn::make('professional.name')
                    ->label('Profissional'),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Serviço'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (AppointmentStatus $state): string => $state->label())
                    ->color(fn (AppointmentStatus $state): string => $state->color()),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Abrir')
                    ->url(fn (Appointment $record): string => AppointmentResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
