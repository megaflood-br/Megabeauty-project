<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\TenantStatus;
use App\Enums\UserRole;
use App\Filament\Resources\TenantResource\Pages;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Plataforma';

    protected static ?string $modelLabel = 'Estabelecimento';

    protected static ?string $pluralModelLabel = 'Estabelecimentos';

    protected static ?int $navigationSort = 1;

    protected static bool $isScopedToTenant = false;

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->role === UserRole::Owner;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identidade')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('subdomain')
                            ->label('Subdomínio')
                            ->required()
                            ->alphaDash()
                            ->maxLength(63)
                            ->unique(ignoreRecord: true),
                        Forms\Components\TextInput::make('custom_domain')
                            ->label('Domínio próprio')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options(collect(TenantStatus::cases())->mapWithKeys(
                                fn (TenantStatus $status): array => [$status->value => $status->label()]
                            ))
                            ->required()
                            ->native(false),
                    ]),
                Forms\Components\Section::make('Contato e preferências')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('owner_email')
                            ->label('E-mail do responsável')
                            ->email()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone')
                            ->tel()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('timezone')
                            ->label('Fuso horário')
                            ->required()
                            ->default('America/Sao_Paulo')
                            ->maxLength(64),
                        Forms\Components\TextInput::make('locale')
                            ->label('Idioma')
                            ->required()
                            ->default('pt_BR')
                            ->maxLength(16),
                        Forms\Components\DateTimePicker::make('trial_ends_at')
                            ->label('Trial até')
                            ->native(false)
                            ->seconds(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('subdomain')
                    ->label('Subdomínio')
                    ->badge()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (TenantStatus $state): string => $state->label())
                    ->color(fn (TenantStatus $state): string => match ($state) {
                        TenantStatus::Active => 'success',
                        TenantStatus::Trial => 'info',
                        TenantStatus::Suspended => 'warning',
                        TenantStatus::Cancelled => 'danger',
                    }),
                Tables\Columns\TextColumn::make('owner_email')
                    ->label('E-mail')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefone')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(collect(TenantStatus::cases())->mapWithKeys(
                        fn (TenantStatus $status): array => [$status->value => $status->label()]
                    )),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return Builder<Tenant>
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user instanceof User) {
            $query->whereKey($user->tenant_id);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
