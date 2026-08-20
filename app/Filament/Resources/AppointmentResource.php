<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\AppointmentSource;
use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?string $modelLabel = 'Agendamento';

    protected static ?string $pluralModelLabel = 'Agendamentos';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Atendimento')
                    ->columns(2)
                    ->schema(static::formSchema()),
            ]);
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function formSchema(): array
    {
        return [
            Forms\Components\Select::make('client_id')
                ->label('Cliente')
                ->relationship('client', 'name')
                ->searchable()
                ->preload()
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('name')->label('Nome')->required(),
                    Forms\Components\TextInput::make('phone')->label('Telefone')->required(),
                ]),
            Forms\Components\Select::make('professional_id')
                ->label('Profissional')
                ->relationship('professional', 'name', fn ($query) => $query->where('is_active', true))
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('service_id')
                ->label('Serviço')
                ->relationship('service', 'name', fn ($query) => $query->where('is_active', true))
                ->searchable()
                ->preload()
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, mixed $state): void {
                    if (! is_numeric($state)) {
                        return;
                    }

                    $service = Service::query()->find((int) $state);

                    if ($service === null) {
                        return;
                    }

                    $set('price', $service->price);

                    $startsAt = $get('starts_at');

                    if (filled($startsAt)) {
                        $set(
                            'ends_at',
                            Carbon::parse((string) $startsAt)
                                ->addMinutes($service->duration_minutes)
                                ->format('Y-m-d H:i:s'),
                        );
                    }
                }),
            Forms\Components\Select::make('status')
                ->label('Status')
                ->options(collect(AppointmentStatus::cases())->mapWithKeys(
                    fn (AppointmentStatus $status): array => [$status->value => $status->label()]
                ))
                ->default(AppointmentStatus::Scheduled->value)
                ->required()
                ->native(false),
            Forms\Components\DateTimePicker::make('starts_at')
                ->label('Início')
                ->required()
                ->native(false)
                ->seconds(false)
                ->live()
                ->afterStateUpdated(function (Set $set, Get $get, mixed $state): void {
                    $serviceId = $get('service_id');

                    if (! filled($state) || ! is_numeric($serviceId)) {
                        return;
                    }

                    $service = Service::query()->find((int) $serviceId);

                    if ($service === null) {
                        return;
                    }

                    $set(
                        'ends_at',
                        Carbon::parse((string) $state)
                            ->addMinutes($service->duration_minutes)
                            ->format('Y-m-d H:i:s'),
                    );
                }),
            Forms\Components\DateTimePicker::make('ends_at')
                ->label('Fim')
                ->required()
                ->native(false)
                ->seconds(false)
                ->after('starts_at'),
            Forms\Components\TextInput::make('price')
                ->label('Valor')
                ->numeric()
                ->prefix('R$')
                ->minValue(0)
                ->step(0.01)
                ->required(),
            Forms\Components\Select::make('source')
                ->label('Origem')
                ->options(collect(AppointmentSource::cases())->mapWithKeys(
                    fn (AppointmentSource $source): array => [$source->value => $source->label()]
                ))
                ->default(AppointmentSource::Manual->value)
                ->required()
                ->native(false),
            Forms\Components\Textarea::make('notes')
                ->label('Observações')
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\TextInput::make('cancellation_reason')
                ->label('Motivo do cancelamento')
                ->maxLength(255)
                ->visible(fn (Get $get): bool => $get('status') === AppointmentStatus::Cancelled->value)
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->columns([
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Horário')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('client.name')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('professional.name')
                    ->label('Profissional')
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->label('Serviço')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (AppointmentStatus $state): string => $state->label())
                    ->color(fn (AppointmentStatus $state): string => $state->color()),
                Tables\Columns\TextColumn::make('price')
                    ->label('Valor')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->label('Origem')
                    ->formatStateUsing(fn (AppointmentSource $state): string => $state->label())
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(AppointmentStatus::cases())->mapWithKeys(
                        fn (AppointmentStatus $status): array => [$status->value => $status->label()]
                    )),
                Tables\Filters\SelectFilter::make('professional_id')
                    ->label('Profissional')
                    ->relationship('professional', 'name'),
                Tables\Filters\Filter::make('starts_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('De')->native(false),
                        Forms\Components\DatePicker::make('until')->label('Até')->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('starts_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('starts_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
