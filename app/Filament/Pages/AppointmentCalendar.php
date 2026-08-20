<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AppointmentStatus;
use App\Enums\FinancialTransactionStatus;
use App\Enums\FinancialTransactionType;
use App\Filament\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\FinancialTransaction;
use App\Models\Professional;
use App\Support\Agenda\EditAppointmentModal;
use App\Support\Agenda\ResourceTimeline;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AppointmentCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.appointment-calendar';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?string $navigationLabel = 'Agenda';

    protected static ?string $title = 'Agenda';

    protected static ?int $navigationSort = 0;

    public string $date;

    public function mount(): void
    {
        $this->date = now()->toDateString();
    }

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    public function previousDay(): void
    {
        $this->date = CarbonImmutable::parse($this->date)->subDay()->toDateString();
    }

    public function nextDay(): void
    {
        $this->date = CarbonImmutable::parse($this->date)->addDay()->toDateString();
    }

    public function today(): void
    {
        $this->date = now()->toDateString();
    }

    public function timeline(): ResourceTimeline
    {
        return new ResourceTimeline;
    }

    /**
     * @return Collection<int, Professional>
     */
    public function professionals(): Collection
    {
        return Professional::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function appointmentsFor(Professional $professional): Collection
    {
        return $this->dayAppointments()
            ->where('professional_id', $professional->id)
            ->values();
    }

    /**
     * @return Collection<int, Appointment>
     */
    public function dayAppointments(): Collection
    {
        return once(function (): Collection {
            return Appointment::query()
                ->with(['client', 'professional', 'service'])
                ->whereDate('starts_at', $this->date)
                ->orderBy('starts_at')
                ->get();
        });
    }

    public function formattedDate(): string
    {
        return CarbonImmutable::parse($this->date)->locale('pt_BR')->translatedFormat('l, d/m/Y');
    }

    public function editAppointmentAction(): EditAction
    {
        return EditAction::make('editAppointment')
            ->record(fn (array $arguments): Appointment => Appointment::query()
                ->with(['client', 'professional', 'service'])
                ->findOrFail((int) $arguments['record']))
            ->form(EditAppointmentModal::schema())
            ->modalWidth(MaxWidth::SevenExtraLarge)
            ->modalHeading('Editando agendamento')
            ->modalSubmitActionLabel('Salvar')
            ->modalCancelActionLabel('Cancelar')
            ->stickyModalFooter()
            ->extraModalWindowAttributes(['class' => 'mb-agenda-edit-modal'])
            ->modalFooterActionsAlignment(Alignment::End)
            ->successNotificationTitle('Agendamento atualizado')
            ->mutateRecordDataUsing(fn (array $data, Model $record): array => $record instanceof Appointment
                ? EditAppointmentModal::mutateRecordData($record, $data)
                : $data)
            ->mutateFormDataUsing(fn (array $data): array => EditAppointmentModal::mutateFormData($data))
            ->modalFooterActions(function (EditAction $action): array {
                return [
                    Action::make('help')
                        ->label('Ajuda')
                        ->icon('heroicon-o-question-mark-circle')
                        ->color('gray')
                        ->link()
                        ->action(function (): void {
                            Notification::make()
                                ->title('Como editar o horário')
                                ->body('Altere o cliente, a data, o status e os itens do agendamento. Salvar aplica as mudanças na grade.')
                                ->info()
                                ->send();
                        }),
                    Action::make('others')
                        ->label('Outros')
                        ->icon('heroicon-m-chevron-down')
                        ->color('gray')
                        ->outlined()
                        ->form([
                            Select::make('next_status')
                                ->label('Ação')
                                ->options([
                                    AppointmentStatus::Completed->value => 'Marcar como concluído',
                                    AppointmentStatus::NoShow->value => 'Não compareceu',
                                    AppointmentStatus::Cancelled->value => 'Cancelar agendamento',
                                ])
                                ->required()
                                ->native(false),
                        ])
                        ->modalHeading('Outras ações')
                        ->modalSubmitActionLabel('Aplicar')
                        ->action(function (array $data, ?Model $record): void {
                            if (! $record instanceof Appointment) {
                                return;
                            }

                            $payload = ['status' => $data['next_status']];

                            if ($data['next_status'] === AppointmentStatus::Cancelled->value) {
                                $payload['cancelled_at'] = $record->cancelled_at ?? now();
                            }

                            $record->update($payload);

                            Notification::make()
                                ->title('Agendamento atualizado')
                                ->success()
                                ->send();
                        }),
                    $action->getModalCancelAction(),
                    DeleteAction::make()
                        ->label('Excluir')
                        ->successNotificationTitle('Agendamento excluído'),
                    $action->getModalSubmitAction(),
                    Action::make('createOrder')
                        ->label('Criar comanda')
                        ->color('success')
                        ->action(function (?Model $record): void {
                            if (! $record instanceof Appointment) {
                                return;
                            }

                            $exists = FinancialTransaction::query()
                                ->where('appointment_id', $record->id)
                                ->where('status', FinancialTransactionStatus::Pending)
                                ->exists();

                            if ($exists) {
                                Notification::make()
                                    ->title('Este agendamento já tem uma comanda em aberto.')
                                    ->warning()
                                    ->send();

                                return;
                            }

                            FinancialTransaction::query()->create([
                                'appointment_id' => $record->id,
                                'client_id' => $record->client_id,
                                'professional_id' => $record->professional_id,
                                'type' => FinancialTransactionType::Income,
                                'category' => 'comanda',
                                'amount' => $record->price,
                                'status' => FinancialTransactionStatus::Pending,
                                'description' => 'Comanda do agendamento #'.$record->id,
                            ]);

                            Notification::make()
                                ->title('Comanda criada')
                                ->success()
                                ->send();
                        }),
                ];
            });
    }

    public function createUrl(Professional $professional, int $minutes): string
    {
        $startsAt = $this->timeline()->slotDateTime($this->date, $minutes);

        return AppointmentResource::getUrl('create', [
            'professional_id' => $professional->id,
            'starts_at' => $startsAt->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array{top: int, height: int}
     */
    public function blockPosition(Appointment $appointment): array
    {
        $timeline = $this->timeline();

        return [
            'top' => $timeline->topPx($appointment->starts_at),
            'height' => $timeline->heightPx($appointment->starts_at, $appointment->ends_at),
        ];
    }

    /**
     * @return array{start: int, end: int}|null
     */
    public function workingWindow(Professional $professional): ?array
    {
        return $this->timeline()->workingWindow(
            $professional->working_hours,
            CarbonImmutable::parse($this->date),
        );
    }

    public function offHoursStyle(?array $window, string $edge): ?string
    {
        $timeline = $this->timeline();

        if ($window === null) {
            return $edge === 'before'
                ? 'top: 0; height: '.$timeline->gridHeight().'px;'
                : null;
        }

        if ($edge === 'before') {
            $height = (int) max(0, round(($window['start'] - $timeline->startMinutes()) / ResourceTimeline::SLOT_MINUTES * ResourceTimeline::SLOT_HEIGHT_PX));

            return $height > 0 ? "top: 0; height: {$height}px;" : null;
        }

        $top = (int) round(($window['end'] - $timeline->startMinutes()) / ResourceTimeline::SLOT_MINUTES * ResourceTimeline::SLOT_HEIGHT_PX);
        $height = $timeline->gridHeight() - $top;

        return $height > 0 ? "top: {$top}px; height: {$height}px;" : null;
    }

    public function initials(Professional $professional): string
    {
        $parts = preg_split('/\s+/', trim($professional->name)) ?: [];
        $letters = '';

        foreach (array_slice($parts, 0, 2) as $part) {
            $letters .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $letters !== '' ? $letters : 'P';
    }

    public function isOccupied(Appointment $appointment): bool
    {
        return $appointment->status === AppointmentStatus::Cancelled
            || $appointment->status === AppointmentStatus::NoShow;
    }
}
