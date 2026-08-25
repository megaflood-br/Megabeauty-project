<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\PriceTableResource\Pages;
use App\Models\PriceTable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PriceTableResource extends Resource
{
    protected static ?string $model = PriceTable::class;

    protected static ?string $navigationIcon = 'heroicon-o-table-cells';

    protected static ?string $navigationGroup = 'Agentes IA';

    protected static ?string $modelLabel = 'Tabela de preço';

    protected static ?string $pluralModelLabel = 'Tabelas de preço';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Tabela')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Vigente para o agente')
                            ->default(true),
                        Forms\Components\DatePicker::make('valid_from')
                            ->label('Válida de')
                            ->native(false),
                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Válida até')
                            ->native(false),
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('Itens e valores')
                    ->description('O agente consulta só os itens desta tabela quando o cliente pede um preço.')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Item')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2),
                                Forms\Components\Select::make('product_id')
                                    ->label('Produto (opcional)')
                                    ->relationship(
                                        name: 'product',
                                        titleAttribute: 'name',
                                        modifyQueryUsing: fn ($query) => $query->where('is_active', true)->orderBy('name'),
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                                Forms\Components\TextInput::make('sku')
                                    ->label('SKU')
                                    ->maxLength(60),
                                Forms\Components\TextInput::make('unit')
                                    ->label('Unidade')
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('price')
                                    ->label('Preço')
                                    ->numeric()
                                    ->required()
                                    ->prefix('R$')
                                    ->minValue(0)
                                    ->step(0.01),
                                Forms\Components\TextInput::make('sort_order')
                                    ->label('Ordem')
                                    ->numeric()
                                    ->default(0),
                                Forms\Components\Textarea::make('notes')
                                    ->label('Observação')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->collapsible()
                            ->addActionLabel('Adicionar item')
                            ->orderColumn('sort_order'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Tabela')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Itens'),
                Tables\Columns\TextColumn::make('valid_from')
                    ->label('De')
                    ->date('d/m/Y')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Até')
                    ->date('d/m/Y')
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativa')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativa')
                    ->boolean()
                    ->placeholder('Todas'),
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
            'index' => Pages\ListPriceTables::route('/'),
            'create' => Pages\CreatePriceTable::route('/create'),
            'edit' => Pages\EditPriceTable::route('/{record}/edit'),
        ];
    }
}
