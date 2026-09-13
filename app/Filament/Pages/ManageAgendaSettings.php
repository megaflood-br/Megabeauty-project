<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AgendaSlotInterval;
use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageAgendaSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.manage-agenda-settings';

    protected static ?string $navigationGroup = 'Agenda';

    protected static ?string $navigationLabel = 'Intervalo da grade';

    protected static ?string $title = 'Intervalo da agenda';

    protected static ?int $navigationSort = 2;

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && in_array($user->role, [UserRole::Owner, UserRole::Admin], true);
    }

    public function mount(): void
    {
        $this->form->fill([
            'agenda_slot_minutes' => $this->tenant()?->agendaSlotMinutes() ?? AgendaSlotInterval::Fifteen->value,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Grade de horários')
                    ->description('Também dá para mudar este intervalo no topo da Agenda, no campo “Intervalo da grade”. A coluna de horários da esquerda acompanha a opção escolhida.')
                    ->schema([
                        Forms\Components\Select::make('agenda_slot_minutes')
                            ->label('Intervalo da agenda')
                            ->options(AgendaSlotInterval::options())
                            ->required()
                            ->native(false)
                            ->helperText('A empresa pode configurar de 5 em 5 minutos, 10 em 10, 15, 20, 30 ou 1 em 1 hora.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $tenant = $this->tenant();

        if ($tenant === null) {
            Notification::make()
                ->title('Nenhum estabelecimento selecionado.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $tenant->setAgendaSlotMinutes((int) $data['agenda_slot_minutes']);
        $tenant->save();

        Notification::make()
            ->title('Intervalo da agenda atualizado')
            ->success()
            ->send();
    }

    private function tenant(): ?Tenant
    {
        $tenant = tenant();

        return $tenant instanceof Tenant ? $tenant : null;
    }
}
