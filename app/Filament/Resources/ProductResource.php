<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Agentes IA';

    protected static ?string $modelLabel = 'Produto';

    protected static ?string $pluralModelLabel = 'Produtos';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Produto')
                    ->columns(2)
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Foto')
                            ->image()
                            ->directory('products')
                            ->disk('public')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->maxLength(60)
                            ->unique(
                                ignoreRecord: true,
                                modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('tenant_id', tenant_id()),
                            ),
                        Forms\Components\TextInput::make('brand')
                            ->label('Marca')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('category')
                            ->label('Categoria')
                            ->maxLength(100)
                            ->datalist(['cabelo', 'unhas', 'estetica', 'finalizadores', 'kits']),
                        Forms\Components\TextInput::make('price')
                            ->label('Preço')
                            ->numeric()
                            ->required()
                            ->prefix('R$')
                            ->minValue(0)
                            ->step(0.01),
                        Forms\Components\TextInput::make('promotional_price')
                            ->label('Preço promocional')
                            ->numeric()
                            ->prefix('R$')
                            ->minValue(0)
                            ->step(0.01),
                        Forms\Components\TextInput::make('unit')
                            ->label('Unidade')
                            ->default('un')
                            ->maxLength(20),
                        Forms\Components\TextInput::make('stock_quantity')
                            ->label('Estoque')
                            ->numeric()
                            ->minValue(0),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Disponível para o agente')
                            ->default(true),
                        Forms\Components\Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->circular()
                    ->size(32),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): ?string => $record->brand),
                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Preço')
                    ->money('BRL')
                    ->sortable(),
                Tables\Columns\TextColumn::make('promotional_price')
                    ->label('Promo')
                    ->money('BRL')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('stock_quantity')
                    ->label('Estoque')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoria'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Ativo')
                    ->boolean()
                    ->placeholder('Todos'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
