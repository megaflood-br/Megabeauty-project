<?php

declare(strict_types=1);

namespace App\Services\OpenAI;

use App\Models\Client;
use App\Models\Professional;
use App\Models\Service;
use App\Models\Tenant;
use App\Services\OpenAI\DTOs\ChatMessage;
use App\Services\OpenAI\DTOs\SuggestionContext;
use App\Services\OpenAI\DTOs\SuggestionResult;
use App\Services\OpenAI\Exceptions\OpenAIException;

final class OpenAiService
{
    public function __construct(
        private readonly OpenAIClient $client,
        private readonly AppointmentSuggestionService $suggestions,
    ) {}

    /**
     * @param  list<ChatMessage|array{role: string, content: string}>  $conversation
     */
    public function suggestAppointments(array $conversation, ?SuggestionContext $context = null): SuggestionResult
    {
        return $this->suggestions->suggest(
            $conversation,
            $context ?? $this->contextFromCurrentTenant(),
        );
    }

    /**
     * Generate a WhatsApp-ready reply from the client conversation.
     *
     * @param  list<ChatMessage|array{role: string, content: string}>  $conversation
     */
    public function generateWhatsAppReply(array $conversation, ?SuggestionContext $context = null): string
    {
        $context ??= $this->contextFromCurrentTenant();

        $messages = [
            new ChatMessage('system', $this->whatsAppSystemPrompt()),
            new ChatMessage('system', 'Contexto do estabelecimento: '.json_encode(
                $context->toArray(),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            )),
        ];

        foreach ($conversation as $item) {
            $messages[] = $item instanceof ChatMessage ? $item : ChatMessage::fromArray($item);
        }

        $response = $this->client->chat($messages, [
            'response_format' => null,
        ]);

        $content = data_get($response, 'choices.0.message.content');

        if (! is_string($content) || trim($content) === '') {
            throw OpenAIException::invalidResponse('missing WhatsApp reply content.');
        }

        return trim($content);
    }

    public function contextFromCurrentTenant(?Client $client = null): SuggestionContext
    {
        $tenant = tenant() ?? new Tenant(['name' => 'Estabelecimento', 'timezone' => 'America/Sao_Paulo']);

        return new SuggestionContext(
            tenantName: $tenant->name,
            timezone: $tenant->timezone ?: 'America/Sao_Paulo',
            client: $client === null ? [] : [
                'name' => $client->name,
                'phone' => $client->phone,
                'notes' => $client->notes,
            ],
            services: Service::query()
                ->where('is_active', true)
                ->get(['name', 'duration_minutes', 'price'])
                ->toArray(),
            professionals: Professional::query()
                ->where('is_active', true)
                ->get(['name'])
                ->toArray(),
        );
    }

    private function whatsAppSystemPrompt(): string
    {
        return <<<'PROMPT'
Você é a secretária virtual de um salão/clínica no WhatsApp.
Responda em português do Brasil, de forma curta, cordial e objetiva.
Se o cliente quiser agendar, confirme serviço, profissional e horário.
Não invente serviços ou profissionais fora do catálogo informado.
Não use markdown.
PROMPT;
    }
}
