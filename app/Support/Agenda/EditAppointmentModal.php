<?php

declare(strict_types=1);

namespace App\Support\Agenda;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

final class EditAppointmentModal
{
    /**
     * @return array<int, Forms\Components\Component>
     */
    public static function schema(): array
    {
        return [
            Forms\Components\Grid::make([
                'default' => 1,
                'lg' => 12,
            ])
                ->schema([
                    Forms\Components\ViewField::make('client_sidebar')
                        ->hiddenLabel()
                        ->dehydrated(false)
                        ->view('filament.agenda.client-sidebar')
                        ->viewData(fn (Get $get, ?Model $record): array => [
                            'selectedClientId' => $get('client_id'),
                            'appointment' => $record,
                        ])
                        ->columnSpan(['default' => 1, 'lg' => 3])
                        ->extraAttributes(['class' => 'mb-agenda-client-pane']),
                    Forms\Components\Group::make(self::mainSchema())
                        ->columnSpan(['default' => 1, 'lg' => 9])
                        ->extraAttributes(['class' => 'mb-agenda-edit-main']),
                ]),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function mainSchema(): array
    {
        return [
            Forms\Components\Placeholder::make('modal_title')
                ->hiddenLabel()
                ->content(new HtmlString('<h2 class="mb-agenda-edit-title">Editando agendamento</h2>')),
            Forms\Components\Grid::make([
                'default' => 1,
                'md' => 12,
            ])->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Cliente')
                    ->relationship('client', 'name')
                    ->getOptionLabelFromRecordUsing(
                        fn (Model $record): string => $record instanceof Client
                            ? $record->name.' · '.$record->formattedPhone()
                            : (string) $record->getAttribute('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live()
                    ->columnSpan(['default' => 1, 'md' => 4])
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->label('Nome')->required(),
                        Forms\Components\TextInput::make('phone')->label('Telefone')->required(),
                    ]),
                Forms\Components\DatePicker::make('date')
                    ->label('Data')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->required()
                    ->live()
                    ->columnSpan(['default' => 1, 'md' => 3]),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options(self::statusOptions())
                    ->allowHtml()
                    ->required()
                    ->native(false)
                    ->columnSpan(['default' => 1, 'md' => 3]),
                Forms\Components\Select::make('color')
                    ->label('Cor')
                    ->options(self::colorOptions())
                    ->allowHtml()
                    ->native(false)
                    ->columnSpan(['default' => 1, 'md' => 2]),
            ]),
            Forms\Components\Placeholder::make('items_heading')
                ->hiddenLabel()
                ->content(new HtmlString(
                    '<div class="mb-agenda-items-head">'.
                    '<span>Itens do agendamento</span>'.
                    '<div class="mb-agenda-items-cols">'.
                    '<span>Descrição</span><span>Profissional</span><span>Horário</span><span>Duração</span>'.
                    '</div></div>',
                )),
            Forms\Components\Repeater::make('items')
                ->hiddenLabel()
                ->schema(self::itemSchema())
                ->defaultItems(1)
                ->minItems(1)
                ->reorderable(false)
                ->addActionLabel('Adicionar item')
                ->deleteAction(fn (FormAction $action): FormAction => $action
                    ->icon('heroicon-o-trash')
                    ->color('danger'))
                ->extraAttributes(['class' => 'mb-agenda-items']),
            Forms\Components\Grid::make([
                'default' => 1,
                'md' => 12,
            ])->schema([
                Forms\Components\Toggle::make('send_reminder')
                    ->label('Enviar lembrete')
                    ->inline(false)
                    ->default(true)
                    ->columnSpan(['default' => 1, 'md' => 3]),
                Forms\Components\Toggle::make('squeeze')
                    ->label('Encaixar agendamento')
                    ->inline(false)
                    ->columnSpan(['default' => 1, 'md' => 3]),
                Forms\Components\Select::make('repeat_rule')
                    ->label('Além deste, repetir mais')
                    ->options([
                        'none' => 'Agendamento não se repete',
                        'weekly' => 'Toda semana',
                        'biweekly' => 'A cada 15 dias',
                        'monthly' => 'Todo mês',
                    ])
                    ->default('none')
                    ->native(false)
                    ->columnSpan(['default' => 1, 'md' => 6]),
            ]),
            Forms\Components\Textarea::make('notes')
                ->label('Observações')
                ->placeholder('Escreva aqui')
                ->rows(3)
                ->columnSpanFull(),
            Forms\Components\Hidden::make('source'),
            Forms\Components\Hidden::make('price'),
            Forms\Components\Hidden::make('record_id')->dehydrated(false),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function itemSchema(): array
    {
        return [
            Forms\Components\Grid::make(12)->schema([
                Forms\Components\Select::make('service_id')
                    ->hiddenLabel()
                    ->placeholder('Descrição')
                    ->options(fn (): array => Service::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function (Set $set, mixed $state): void {
                        if (! is_numeric($state)) {
                            return;
                        }

                        $service = Service::query()->find((int) $state);

                        if ($service !== null) {
                            $set('duration', $service->duration_minutes);
                        }
                    })
                    ->columnSpan(5),
                Forms\Components\Select::make('professional_id')
                    ->hiddenLabel()
                    ->placeholder('Profissional')
                    ->options(fn (): array => Professional::query()
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->required()
                    ->live()
                    ->columnSpan(3),
                Forms\Components\Select::make('time')
                    ->hiddenLabel()
                    ->placeholder('Horário')
                    ->options(fn (Get $get): array => self::timeOptions(
                        professionalId: is_numeric($get('professional_id')) ? (int) $get('professional_id') : null,
                        date: filled($get('../../date')) ? (string) $get('../../date') : null,
                        exceptAppointmentId: is_numeric($get('../../record_id')) ? (int) $get('../../record_id') : null,
                    ))
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->columnSpan(2),
                Forms\Components\Select::make('duration')
                    ->hiddenLabel()
                    ->placeholder('Duração')
                    ->options(fn (Get $get): array => self::durationOptions(
                        is_numeric($get('duration')) ? (int) $get('duration') : null,
                    ))
                    ->required()
                    ->native(false)
                    ->columnSpan(2),
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutateRecordData(Appointment $record, array $data): array
    {
        $data['date'] = $record->starts_at?->toDateString();
        $data['status'] = $record->status instanceof AppointmentStatus
            ? $record->status->value
            : $data['status'] ?? AppointmentStatus::Scheduled->value;
        $data['color'] = $record->color ?: ($record->professional?->color ?: '#059669');
        $data['send_reminder'] = $record->send_reminder ?? true;
        $data['squeeze'] = $record->squeeze ?? false;
        $data['repeat_rule'] = $record->repeat_rule ?: 'none';
        $data['items'] = self::itemsFromRecord($record);
        $data['source'] = $record->source?->value ?? $data['source'] ?? null;
        $data['price'] = $record->price;
        $data['record_id'] = $record->id;

        unset($data['client_sidebar']);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutateFormData(array $data): array
    {
        $items = array_values(array_filter(
            is_array($data['items'] ?? null) ? $data['items'] : [],
            fn (mixed $item): bool => is_array($item),
        ));

        $first = $items[0] ?? [];
        $date = (string) ($data['date'] ?? now()->toDateString());
        $time = (string) ($first['time'] ?? '09:00');
        $startsAt = Carbon::parse($date.' '.$time);

        $totalMinutes = 0;
        $totalPrice = 0.0;

        foreach ($items as $item) {
            $duration = max(ResourceTimeline::SLOT_MINUTES, (int) ($item['duration'] ?? ResourceTimeline::SLOT_MINUTES));
            $totalMinutes += $duration;

            if (is_numeric($item['service_id'] ?? null)) {
                $service = Service::query()->find((int) $item['service_id']);
                $totalPrice += (float) ($service?->price ?? 0);
            }
        }

        $data['professional_id'] = $first['professional_id'] ?? null;
        $data['service_id'] = $first['service_id'] ?? null;
        $data['starts_at'] = $startsAt->format('Y-m-d H:i:s');
        $data['ends_at'] = $startsAt->copy()->addMinutes(max(ResourceTimeline::SLOT_MINUTES, $totalMinutes))->format('Y-m-d H:i:s');
        $data['price'] = $totalPrice;
        $data['items'] = $items;
        $data['color'] = $data['color'] ?? null;
        $data['repeat_rule'] = $data['repeat_rule'] ?? 'none';

        if (($data['status'] ?? null) === AppointmentStatus::Cancelled->value) {
            $data['cancelled_at'] = $data['cancelled_at'] ?? now();
        }

        if (($data['status'] ?? null) === AppointmentStatus::Confirmed->value) {
            $data['confirmed_at'] = $data['confirmed_at'] ?? now();
        }

        unset($data['date'], $data['client_sidebar'], $data['record_id']);

        return $data;
    }

    /**
     * @return array<string, string>
     */
    public static function timeOptions(?int $professionalId, ?string $date, ?int $exceptAppointmentId): array
    {
        $timeline = new ResourceTimeline;
        $busy = collect();

        if ($professionalId !== null && filled($date)) {
            $busy = Appointment::query()
                ->where('professional_id', $professionalId)
                ->whereDate('starts_at', $date)
                ->when($exceptAppointmentId !== null, fn ($query) => $query->where('id', '!=', $exceptAppointmentId))
                ->whereNotIn('status', [
                    AppointmentStatus::Cancelled->value,
                    AppointmentStatus::NoShow->value,
                ])
                ->get(['starts_at', 'ends_at']);
        }

        $options = [];

        foreach ($timeline->slots() as $slot) {
            $label = $slot['label'];
            $slotStart = filled($date)
                ? $timeline->slotDateTime($date, $slot['minutes'])
                : null;
            $slotEnd = $slotStart?->addMinutes(ResourceTimeline::SLOT_MINUTES);

            $unavailable = $slotStart !== null && $busy->contains(
                function (Appointment $appointment) use ($slotStart, $slotEnd): bool {
                    return $appointment->starts_at < $slotEnd
                        && $appointment->ends_at > $slotStart;
                },
            );

            $options[$label] = $unavailable ? "{$label} (Indisponível)" : $label;
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public static function durationOptions(?int $current = null): array
    {
        $minutesList = range(15, 240, 15);

        if ($current !== null && $current > 0 && ! in_array($current, $minutesList, true)) {
            $minutesList[] = $current;
            sort($minutesList);
        }

        $options = [];

        foreach ($minutesList as $minutes) {
            $hours = intdiv($minutes, 60);
            $rest = $minutes % 60;
            $label = $hours > 0
                ? ($rest > 0 ? "{$hours}h {$rest}min" : "{$hours}h")
                : "{$minutes} min";

            $options[$minutes] = $label;
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    public static function colorOptions(): array
    {
        $colors = [
            '#059669' => 'Verde',
            '#10b981' => 'Esmeralda',
            '#eab308' => 'Amarelo',
            '#f97316' => 'Laranja',
            '#ef4444' => 'Vermelho',
            '#3b82f6' => 'Azul',
            '#8b5cf6' => 'Roxo',
            '#64748b' => 'Cinza',
        ];

        return collect($colors)
            ->mapWithKeys(fn (string $label, string $hex): array => [
                $hex => '<span class="mb-agenda-color-option"><span class="mb-agenda-color-dot" style="background: '.$hex.'"></span>'.$label.'</span>',
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return collect(AppointmentStatus::cases())
            ->mapWithKeys(fn (AppointmentStatus $status): array => [
                $status->value => '<span class="mb-agenda-status-option"><span class="mb-agenda-status-dot is-'.$status->value.'"></span>'.$status->label().'</span>',
            ])
            ->all();
    }

    /**
     * @return list<array{service_id: mixed, professional_id: mixed, time: string, duration: int}>
     */
    private static function itemsFromRecord(Appointment $record): array
    {
        if (is_array($record->items) && $record->items !== []) {
            return array_values($record->items);
        }

        $duration = 60;

        if ($record->starts_at !== null && $record->ends_at !== null) {
            $duration = max(ResourceTimeline::SLOT_MINUTES, (int) $record->starts_at->diffInMinutes($record->ends_at));
        }

        return [[
            'service_id' => $record->service_id,
            'professional_id' => $record->professional_id,
            'time' => $record->starts_at?->format('H:i') ?? '09:00',
            'duration' => $duration,
        ]];
    }
}
