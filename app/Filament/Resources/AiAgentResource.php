<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Enums\AiAgentEmojiUsage;
use App\Enums\AiAgentReplyLength;
use App\Enums\AiAgentRole;
use App\Enums\AiAgentTone;
use App\Filament\Resources\AiAgentResource\Pages;
use App\Models\AiAgent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class AiAgentResource extends Resource
{
    protected static ?string $model = AiAgent::class;

    protected static ?string $navigationIcon = 'heroicon-o-face-smile';

    protected static ?string $navigationGroup = 'Agentes IA';

    protected static ?string $modelLabel = 'Agente';

    protected static ?string $pluralModelLabel = 'Agentes';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Agente')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Identidade e avatar')
                            ->icon('heroicon-o-user-circle')
                            ->schema(self::identitySchema()),
                        Forms\Components\Tabs\Tab::make('Forma de atendimento')
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->schema(self::attendanceSchema()),
                        Forms\Components\Tabs\Tab::make('Modelo OpenAI')
                            ->icon('heroicon-o-cpu-chip')
                            ->schema(self::modelSchema()),
                        Forms\Components\Tabs\Tab::make('Perguntas frequentes')
                            ->icon('heroicon-o-question-mark-circle')
                            ->schema(self::faqSchema()),
                    ]),
            ]);
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function identitySchema(): array
    {
        return [
            Forms\Components\Section::make('Avatar')
                ->columns(2)
                ->schema([
                    Forms\Components\FileUpload::make('avatar_path')
                        ->label('Foto do agente')
                        ->image()
                        ->avatar()
                        ->directory('agents/avatars')
                        ->disk('public')
                        ->imageEditor()
                        ->circleCropper(),
                    Forms\Components\Group::make()
                        ->schema([
                            Forms\Components\ColorPicker::make('avatar_color')
                                ->label('Cor do avatar')
                                ->default('#059669'),
                            Forms\Components\TextInput::make('name')
                                ->label('Nome do agente')
                                ->required()
                                ->maxLength(80)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (?string $state, Forms\Set $set, ?AiAgent $record): void {
                                    if ($record?->public_slug) {
                                        return;
                                    }

                                    $set('public_slug', str($state ?? '')->slug()->toString());
                                }),
                            Forms\Components\TextInput::make('public_slug')
                                ->label('Slug público')
                                ->helperText('Usado no chat público: /agente/luna')
                                ->required()
                                ->alphaDash()
                                ->maxLength(80)
                                ->unique(
                                    ignoreRecord: true,
                                    modifyRuleUsing: fn (Unique $rule): Unique => $rule->where('tenant_id', tenant_id()),
                                ),
                        ]),
                    Forms\Components\Select::make('role')
                        ->label('Função')
                        ->options(collect(AiAgentRole::cases())->mapWithKeys(
                            fn (AiAgentRole $role): array => [$role->value => $role->label()]
                        ))
                        ->required()
                        ->native(false),
                    Forms\Components\TextInput::make('greeting')
                        ->label('Saudação')
                        ->placeholder('Oi! Eu sou a Luna, da recepção. Como posso te ajudar?')
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('signature')
                        ->label('Assinatura')
                        ->placeholder('Luna | Salão Demo')
                        ->maxLength(120),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Agente ativo')
                        ->default(true),
                    Forms\Components\Toggle::make('is_default')
                        ->label('Agente padrão')
                        ->helperText('Usado no WhatsApp e no widget quando nenhum outro for escolhido.')
                        ->default(false),
                ]),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function attendanceSchema(): array
    {
        return [
            Forms\Components\Section::make('Tom e ritmo')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('tone')
                        ->label('Tom de voz')
                        ->options(collect(AiAgentTone::cases())->mapWithKeys(
                            fn (AiAgentTone $tone): array => [$tone->value => $tone->label()]
                        ))
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('emoji_usage')
                        ->label('Emojis')
                        ->options(collect(AiAgentEmojiUsage::cases())->mapWithKeys(
                            fn (AiAgentEmojiUsage $usage): array => [$usage->value => $usage->label()]
                        ))
                        ->required()
                        ->native(false),
                    Forms\Components\Select::make('reply_length')
                        ->label('Tamanho da resposta')
                        ->options(collect(AiAgentReplyLength::cases())->mapWithKeys(
                            fn (AiAgentReplyLength $length): array => [$length->value => $length->label()]
                        ))
                        ->required()
                        ->native(false),
                ]),
            Forms\Components\Section::make('Roteiro')
                ->schema([
                    Forms\Components\Textarea::make('attendance_script')
                        ->label('Como deve atender')
                        ->placeholder('Cumprimente, entenda o pedido, consulte preços e ofereça agendar.')
                        ->rows(4),
                    Forms\Components\Textarea::make('closing_script')
                        ->label('Como deve encerrar')
                        ->rows(3),
                    Forms\Components\Textarea::make('custom_instructions')
                        ->label('Instruções extras')
                        ->rows(4),
                    Forms\Components\Textarea::make('forbidden_topics')
                        ->label('Assuntos proibidos')
                        ->rows(3),
                    Forms\Components\Textarea::make('handoff_rules')
                        ->label('Quando passar para um humano')
                        ->rows(3),
                    Forms\Components\TextInput::make('handoff_phone')
                        ->label('Telefone para encaminhamento')
                        ->tel()
                        ->maxLength(20),
                ]),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function modelSchema(): array
    {
        return [
            Forms\Components\Section::make('OpenAI')
                ->description('O catálogo não vai inteiro no prompt: o agente consulta produtos, serviços e a empresa só quando o cliente pede.')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('model')
                        ->label('Modelo')
                        ->options(config('openai.models'))
                        ->required()
                        ->native(false)
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('temperature')
                        ->label('Criatividade')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(1.5)
                        ->step(0.1)
                        ->default(0.4)
                        ->helperText('0 = mais fiel ao catálogo. 0.4 é um bom equilíbrio.'),
                    Forms\Components\TextInput::make('max_tokens')
                        ->label('Limite de tokens')
                        ->numeric()
                        ->minValue(100)
                        ->maxValue(2000)
                        ->default(600),
                    Forms\Components\CheckboxList::make('tools')
                        ->label('Ferramentas que o agente pode usar')
                        ->options(AiAgent::availableTools())
                        ->columns(2)
                        ->default(AiAgent::defaultTools())
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    private static function faqSchema(): array
    {
        return [
            Forms\Components\Repeater::make('faqs')
                ->label('Perguntas que o agente já sabe de cor')
                ->schema([
                    Forms\Components\TextInput::make('question')
                        ->label('Pergunta')
                        ->required()
                        ->maxLength(180),
                    Forms\Components\Textarea::make('answer')
                        ->label('Resposta')
                        ->required()
                        ->rows(2),
                ])
                ->defaultItems(0)
                ->collapsible()
                ->addActionLabel('Adicionar pergunta')
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar_path')
                    ->label('')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn (AiAgent $record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&background=059669&color=fff')
                    ->size(36),
                Tables\Columns\TextColumn::make('name')
                    ->label('Agente')
                    ->searchable()
                    ->sortable()
                    ->description(fn (AiAgent $record): string => $record->role->label()),
                Tables\Columns\TextColumn::make('tone')
                    ->label('Tom')
                    ->badge()
                    ->formatStateUsing(fn (AiAgentTone $state): string => $state->label()),
                Tables\Columns\TextColumn::make('model')
                    ->label('Modelo')
                    ->badge()
                    ->color('gray'),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Padrão')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Ativo')
                    ->boolean(),
            ])
            ->filters([
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
            'index' => Pages\ListAiAgents::route('/'),
            'create' => Pages\CreateAiAgent::route('/create'),
            'edit' => Pages\EditAiAgent::route('/{record}/edit'),
        ];
    }
}
