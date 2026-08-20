<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\EvolutionApiSetting;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageEvolutionApiSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static string $view = 'filament.pages.manage-evolution-api-settings';

    protected static ?string $navigationGroup = 'Integrações';

    protected static ?string $navigationLabel = 'WhatsApp (Evolution)';

    protected static ?string $title = 'Configurações da Evolution API';

    protected static ?int $navigationSort = 10;

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        $settings = EvolutionApiSetting::query()->first();

        $this->form->fill([
            'url' => $settings?->url,
            'instance_name' => $settings?->instance_name,
            'token' => $settings?->token,
            'webhook_url' => $settings?->webhook_url,
            'is_active' => $settings?->is_active ?? true,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Instância WhatsApp')
                    ->description('Cada estabelecimento usa a própria instância da Evolution API.')
                    ->schema([
                        Forms\Components\TextInput::make('url')
                            ->label('URL da API')
                            ->placeholder('https://evolution.seudominio.com')
                            ->url()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('instance_name')
                            ->label('Nome da instância')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('token')
                            ->label('Token')
                            ->password()
                            ->revealable()
                            ->required()
                            ->maxLength(500),
                        Forms\Components\TextInput::make('webhook_url')
                            ->label('Webhook (opcional)')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Integração ativa')
                            ->default(true),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $state = $this->form->getState();

        EvolutionApiSetting::query()->updateOrCreate(
            ['tenant_id' => tenant_id()],
            [
                'url' => $state['url'],
                'instance_name' => $state['instance_name'],
                'token' => $state['token'],
                'webhook_url' => $state['webhook_url'] ?? null,
                'is_active' => (bool) ($state['is_active'] ?? true),
            ],
        );

        Notification::make()
            ->title('Configurações da Evolution API salvas.')
            ->success()
            ->send();
    }
}
