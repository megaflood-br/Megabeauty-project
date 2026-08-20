<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Client;
use App\Services\OpenAI\OpenAiService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Throwable;

class AiAssistant extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static string $view = 'filament.pages.ai-assistant';

    protected static ?string $navigationGroup = 'Integrações';

    protected static ?string $navigationLabel = 'Assistente IA';

    protected static ?string $title = 'Assistente de agendamento (OpenAI)';

    protected static ?int $navigationSort = 20;

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public ?string $result = null;

    public function mount(): void
    {
        $this->form->fill([
            'mode' => 'whatsapp',
            'conversation' => '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('client_id')
                    ->label('Cliente (opcional)')
                    ->options(fn () => Client::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Forms\Components\Select::make('mode')
                    ->label('Tipo de resposta')
                    ->options([
                        'whatsapp' => 'Resposta automática para WhatsApp',
                        'suggestion' => 'Sugestão estruturada de agendamento',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\Textarea::make('conversation')
                    ->label('Histórico da conversa')
                    ->placeholder('Cliente: Oi, quero marcar um corte amanhã à tarde')
                    ->rows(8)
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Gerar com GPT-4o-mini')
                ->color('primary')
                ->action('generate'),
        ];
    }

    public function generate(): void
    {
        $state = $this->form->getState();
        $conversation = $this->parseConversation((string) ($state['conversation'] ?? ''));

        $client = isset($state['client_id']) && is_numeric($state['client_id'])
            ? Client::query()->find((int) $state['client_id'])
            : null;

        $openai = app(OpenAiService::class);
        $context = $openai->contextFromCurrentTenant($client);

        try {
            if (($state['mode'] ?? 'whatsapp') === 'suggestion') {
                $suggestion = $openai->suggestAppointments($conversation, $context);
                $this->result = json_encode($suggestion->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } else {
                $this->result = $openai->generateWhatsAppReply($conversation, $context);
            }

            Notification::make()->title('Resposta gerada.')->success()->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Não foi possível consultar a OpenAI')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    private function parseConversation(string $raw): array
    {
        $lines = preg_split('/\R+/', trim($raw)) ?: [];
        $messages = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (preg_match('/^(cliente|user|usuário|usuario)\s*:\s*(.+)$/iu', $line, $matches) === 1) {
                $messages[] = ['role' => 'user', 'content' => $matches[2]];

                continue;
            }

            if (preg_match('/^(assistente|assistant|secretaria|bot)\s*:\s*(.+)$/iu', $line, $matches) === 1) {
                $messages[] = ['role' => 'assistant', 'content' => $matches[2]];

                continue;
            }

            $messages[] = ['role' => 'user', 'content' => $line];
        }

        return $messages;
    }
}
