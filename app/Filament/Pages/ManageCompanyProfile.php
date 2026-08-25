<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\CompanyProfile;
use App\Rules\CnpjRule;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ManageCompanyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static string $view = 'filament.pages.manage-company-profile';

    protected static ?string $navigationGroup = 'Agentes IA';

    protected static ?string $navigationLabel = 'Empresa';

    protected static ?string $title = 'Dados da empresa';

    protected static ?string $slug = 'company-profile';

    protected static ?int $navigationSort = 2;

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public function mount(): void
    {
        $profile = CompanyProfile::query()->first();

        $this->form->fill($profile?->toArray() ?? [
            'trade_name' => tenant()?->name,
            'phone' => tenant()?->phone,
            'opening_hours' => self::defaultOpeningHours(),
            'payment_methods' => ['Pix', 'Débito', 'Crédito', 'Dinheiro'],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identificação')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->directory('company/logos')
                            ->disk('public')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('trade_name')
                            ->label('Nome fantasia')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('legal_name')
                            ->label('Razão social')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('cnpj')
                            ->label('CNPJ')
                            ->mask('99.999.999/9999-99')
                            ->rule(new CnpjRule)
                            ->maxLength(18),
                        Forms\Components\TextInput::make('state_registration')
                            ->label('Inscrição estadual')
                            ->maxLength(30),
                        Forms\Components\TextInput::make('municipal_registration')
                            ->label('Inscrição municipal')
                            ->maxLength(30),
                    ]),
                Forms\Components\Section::make('Contato')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('whatsapp')
                            ->label('WhatsApp')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('website')
                            ->label('Site')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('instagram')
                            ->label('Instagram')
                            ->maxLength(80),
                        Forms\Components\TextInput::make('facebook')
                            ->label('Facebook')
                            ->maxLength(80),
                        Forms\Components\TextInput::make('tiktok')
                            ->label('TikTok')
                            ->maxLength(80),
                    ]),
                Forms\Components\Section::make('Endereço')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('address_zip')
                            ->label('CEP')
                            ->mask('99999-999')
                            ->maxLength(9),
                        Forms\Components\TextInput::make('address_street')
                            ->label('Rua')
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('address_number')
                            ->label('Número')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('address_complement')
                            ->label('Complemento')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('address_neighborhood')
                            ->label('Bairro')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('address_city')
                            ->label('Cidade')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('address_state')
                            ->label('UF')
                            ->maxLength(2),
                    ]),
                Forms\Components\Section::make('Atendimento')
                    ->schema([
                        Forms\Components\Repeater::make('opening_hours')
                            ->label('Horário de funcionamento')
                            ->schema([
                                Forms\Components\Select::make('weekday')
                                    ->label('Dia')
                                    ->options([
                                        'monday' => 'Segunda',
                                        'tuesday' => 'Terça',
                                        'wednesday' => 'Quarta',
                                        'thursday' => 'Quinta',
                                        'friday' => 'Sexta',
                                        'saturday' => 'Sábado',
                                        'sunday' => 'Domingo',
                                    ])
                                    ->required()
                                    ->native(false),
                                Forms\Components\TimePicker::make('open')
                                    ->label('Abre')
                                    ->seconds(false),
                                Forms\Components\TimePicker::make('close')
                                    ->label('Fecha')
                                    ->seconds(false),
                                Forms\Components\Toggle::make('closed')
                                    ->label('Fechado'),
                            ])
                            ->columns(4)
                            ->default(self::defaultOpeningHours())
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                        Forms\Components\TagsInput::make('payment_methods')
                            ->label('Formas de pagamento')
                            ->placeholder('Pix, débito, crédito…'),
                        Forms\Components\Textarea::make('about')
                            ->label('Sobre a empresa')
                            ->rows(4),
                        Forms\Components\Textarea::make('policies')
                            ->label('Políticas (cancelamento, atrasos, menores)')
                            ->rows(4),
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

        CompanyProfile::query()->updateOrCreate(
            ['tenant_id' => tenant_id()],
            $state,
        );

        Notification::make()
            ->title('Dados da empresa salvos.')
            ->body('O agente passa a consultar CNPJ, endereço e horários por esta ficha.')
            ->success()
            ->send();
    }

    /**
     * @return list<array{weekday: string, open: ?string, close: ?string, closed: bool}>
     */
    public static function defaultOpeningHours(): array
    {
        return [
            ['weekday' => 'monday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
            ['weekday' => 'tuesday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
            ['weekday' => 'wednesday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
            ['weekday' => 'thursday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
            ['weekday' => 'friday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
            ['weekday' => 'saturday', 'open' => '08:00', 'close' => '14:00', 'closed' => false],
            ['weekday' => 'sunday', 'open' => null, 'close' => null, 'closed' => true],
        ];
    }
}
