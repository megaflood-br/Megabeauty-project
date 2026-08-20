<?php

declare(strict_types=1);

namespace Tests\Unit\Services\OpenAI;

use App\Services\OpenAI\DTOs\ChatMessage;
use App\Services\OpenAI\DTOs\SuggestionContext;
use App\Services\OpenAI\OpenAiService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class OpenAiServiceTest extends TestCase
{
    public function test_generates_a_whatsapp_reply_from_conversation_history(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => 'Posso agendar seu corte amanhã às 14h com a Ana. Confirma?',
                    ],
                ]],
            ], 200),
        ]);

        $reply = app(OpenAiService::class)->generateWhatsAppReply(
            [new ChatMessage('user', 'Quero um corte amanhã à tarde')],
            new SuggestionContext(
                tenantName: 'Salão Demo',
                services: [['name' => 'Corte']],
                professionals: [['name' => 'Ana']],
            ),
        );

        $this->assertStringContainsString('14h', $reply);

        Http::assertSent(fn ($request): bool => ! isset($request['response_format']));
    }
}
