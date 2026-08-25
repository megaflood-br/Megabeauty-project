<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\AiAgentChannel;
use App\Filament\Resources\AiAgentResource;
use App\Models\AiAgent;
use App\Models\AiAgentConversation;
use App\Services\Agents\AgentRuntime;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Throwable;

class AgentPlayground extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';

    protected static string $view = 'filament.pages.agent-playground';

    protected static ?string $navigationGroup = 'Agentes IA';

    protected static ?string $navigationLabel = 'Testar agente';

    protected static ?string $title = 'Playground do agente';

    protected static ?string $slug = 'agent-playground';

    protected static ?int $navigationSort = 5;

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public ?int $conversationId = null;

    /**
     * @var list<array<string, mixed>>
     */
    public array $messages = [];

    /**
     * @var list<array{name: string, arguments: array<string, mixed>, result_summary: string}>
     */
    public array $lastTraces = [];

    public function mount(): void
    {
        $this->form->fill([
            'ai_agent_id' => AiAgent::query()->where('is_active', true)->orderByDesc('is_default')->value('id'),
            'message' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('ai_agent_id')
                    ->label('Agente')
                    ->options(fn (): Collection => AiAgent::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                    ->required()
                    ->live()
                    ->native(false)
                    ->afterStateUpdated(function (): void {
                        $this->resetConversation();
                    }),
                Forms\Components\Textarea::make('message')
                    ->label('Mensagem do cliente')
                    ->placeholder('Quanto custa o shampoo de hidratação?')
                    ->rows(3)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $state = $this->form->getState();
        $agent = AiAgent::query()->find((int) $state['ai_agent_id']);

        if (! $agent instanceof AiAgent) {
            Notification::make()->title('Selecione um agente.')->danger()->send();

            return;
        }

        $content = trim((string) $state['message']);
        $conversation = $this->currentConversation($agent);

        $conversation->appendMessage([
            'role' => 'user',
            'content' => $content,
            'at' => now()->toIso8601String(),
        ]);

        try {
            $reply = app(AgentRuntime::class)->reply($agent, $conversation->fresh()?->chatTurns() ?? []);

            $conversation->appendMessage([
                'role' => 'assistant',
                'content' => $reply->content,
                'traces' => $reply->toolTraces,
                'at' => now()->toIso8601String(),
            ]);

            $this->lastTraces = $reply->toolTraces;
            $this->messages = $conversation->fresh()?->messages ?? [];
            $this->data['message'] = '';

            Notification::make()->title('Resposta gerada.')->success()->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Não foi possível consultar a OpenAI')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetConversation(): void
    {
        $this->conversationId = null;
        $this->messages = [];
        $this->lastTraces = [];
    }

    /**
     * @return Collection<int, AiAgent>
     */
    public function agents(): Collection
    {
        return AiAgent::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function selectedAgent(): ?AiAgent
    {
        $id = $this->data['ai_agent_id'] ?? null;

        return is_numeric($id) ? AiAgent::query()->find((int) $id) : null;
    }

    public function createAgentUrl(): string
    {
        return AiAgentResource::getUrl('create');
    }

    private function currentConversation(AiAgent $agent): AiAgentConversation
    {
        if ($this->conversationId !== null) {
            $existing = AiAgentConversation::query()->find($this->conversationId);

            if ($existing instanceof AiAgentConversation && (int) $existing->ai_agent_id === (int) $agent->id) {
                return $existing;
            }
        }

        $conversation = AiAgentConversation::query()->create([
            'ai_agent_id' => $agent->id,
            'channel' => AiAgentChannel::Playground,
            'session_key' => (string) str()->uuid(),
            'messages' => [],
        ]);

        $this->conversationId = $conversation->id;

        return $conversation;
    }
}
