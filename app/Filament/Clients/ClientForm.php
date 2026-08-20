<?php

declare(strict_types=1);

namespace App\Filament\Clients;

use App\Models\Client;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ClientForm
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
            ])->schema([
                Forms\Components\Group::make(self::mainSchema())
                    ->columnSpan(['default' => 1, 'lg' => 7])
                    ->extraAttributes(['class' => 'mb-client-main']),
                Forms\Components\Group::make(self::sideSchema())
                    ->columnSpan(['default' => 1, 'lg' => 5])
                    ->extraAttributes(['class' => 'mb-client-side']),
            ]),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function mainSchema(): array
    {
        return [
            Forms\Components\FileUpload::make('avatar_path')
                ->label('Foto')
                ->image()
                ->avatar()
                ->directory('clients/avatars')
                ->disk('public')
                ->imageEditor()
                ->circleCropper()
                ->alignCenter(),
            Forms\Components\TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(255)
                ->live(onBlur: true),
            Forms\Components\TextInput::make('nickname')
                ->label('Apelido')
                ->maxLength(255)
                ->hintIcon('heroicon-o-information-circle', tooltip: 'Como o cliente prefere ser chamado.'),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('phone')
                    ->label('Celular')
                    ->tel()
                    ->required()
                    ->maxLength(20)
                    ->placeholder('(11) 99999-9999'),
                Forms\Components\TextInput::make('landline')
                    ->label('Telefone')
                    ->tel()
                    ->maxLength(20)
                    ->placeholder('(11) 3333-3333'),
            ]),
            Forms\Components\TextInput::make('email')
                ->label('E-mail')
                ->email()
                ->placeholder('E-mail')
                ->maxLength(255),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\DatePicker::make('birth_date')
                    ->label('Aniversário')
                    ->native(false)
                    ->displayFormat('d/m/Y')
                    ->placeholder('01/01/2000'),
                Forms\Components\TextInput::make('cnpj')
                    ->label('CNPJ')
                    ->placeholder('CNPJ')
                    ->maxLength(20),
            ]),
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('document')
                    ->label('CPF')
                    ->placeholder('CPF')
                    ->maxLength(20),
                Forms\Components\TextInput::make('rg')
                    ->label('RG')
                    ->placeholder('RG')
                    ->maxLength(20),
            ]),
            Forms\Components\Repeater::make('dependents')
                ->label('Dependentes')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\DatePicker::make('birth_date')
                        ->label('Aniversário')
                        ->native(false)
                        ->displayFormat('d/m/Y'),
                ])
                ->columns(2)
                ->default([])
                ->reorderable(false)
                ->addActionLabel('+ Adicionar dependente')
                ->itemLabel(fn (array $state): ?string => $state['name'] ?? null),
            Forms\Components\Select::make('referred_by_client_id')
                ->label('Indicado por')
                ->relationship(
                    name: 'referredBy',
                    titleAttribute: 'name',
                    modifyQueryUsing: fn (Builder $query, ?Model $record): Builder => $query
                        ->when(
                            $record instanceof Client,
                            fn (Builder $inner): Builder => $inner->where('id', '!=', $record->getKey()),
                        )
                        ->orderBy('name'),
                )
                ->searchable()
                ->preload()
                ->placeholder('Selecionar cliente'),
            Forms\Components\TagsInput::make('hashtags')
                ->label('Hashtags')
                ->placeholder('Hashtags')
                ->splitKeys(['Tab', 'Enter', ',']),
            Forms\Components\Textarea::make('notes')
                ->label('Observações')
                ->placeholder('Observações')
                ->rows(4),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function sideSchema(): array
    {
        return [
            Forms\Components\Section::make('Endereço')
                ->collapsed()
                ->compact()
                ->schema([
                    Forms\Components\TextInput::make('address_zip')
                        ->label('CEP')
                        ->maxLength(16),
                    Forms\Components\TextInput::make('address_street')
                        ->label('Rua')
                        ->maxLength(255),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('address_number')
                            ->label('Número')
                            ->maxLength(32),
                        Forms\Components\TextInput::make('address_complement')
                            ->label('Complemento')
                            ->maxLength(255),
                    ]),
                    Forms\Components\TextInput::make('address_neighborhood')
                        ->label('Bairro')
                        ->maxLength(255),
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('address_city')
                            ->label('Cidade')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('address_state')
                            ->label('UF')
                            ->maxLength(2),
                    ]),
                ]),
            Forms\Components\Section::make('Redes sociais')
                ->collapsed()
                ->compact()
                ->schema([
                    Forms\Components\TextInput::make('instagram')
                        ->label('Instagram')
                        ->prefix('@')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('facebook')
                        ->label('Facebook')
                        ->maxLength(255),
                    Forms\Components\TextInput::make('tiktok')
                        ->label('TikTok')
                        ->prefix('@')
                        ->maxLength(255),
                ]),
            Forms\Components\Section::make('Configurações')
                ->compact()
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('default_discount_percent')
                            ->label('Desconto padrão')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->default(0),
                        Forms\Components\Select::make('default_discount_apply_on')
                            ->label('Aplicar')
                            ->options([
                                'comanda' => 'Na comanda',
                                'servico' => 'No serviço',
                            ])
                            ->default('comanda')
                            ->native(false),
                    ]),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Ativo')
                        ->default(true)
                        ->helperText('Desative um cliente para que ele não apareça mais em agendamentos, comandas etc.'),
                    Forms\Components\Toggle::make('notifications_enabled')
                        ->label('Notificações')
                        ->default(true)
                        ->helperText('O cliente irá receber notificações (WhatsApp) sobre novos agendamentos, lembretes etc.'),
                    Forms\Components\Toggle::make('access_blocked')
                        ->label('Bloquear acesso')
                        ->helperText('Ao bloquear, o cliente não terá acesso ao agendamento online.'),
                    Forms\Components\Select::make('source')
                        ->label('Origem')
                        ->options([
                            'whatsapp' => 'WhatsApp',
                            'walk_in' => 'Presencial',
                            'instagram' => 'Instagram',
                            'indicacao' => 'Indicação',
                            'online' => 'Online',
                        ])
                        ->native(false),
                ]),
        ];
    }
}
