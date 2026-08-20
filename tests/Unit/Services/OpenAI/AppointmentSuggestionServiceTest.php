<?php

declare(strict_types=1);

namespace Tests\Unit\Services\OpenAI;

use App\Services\OpenAI\AppointmentSuggestionService;
use App\Services\OpenAI\DTOs\ChatMessage;
use App\Services\OpenAI\DTOs\SuggestionContext;
use App\Services\OpenAI\Exceptions\OpenAIException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AppointmentSuggestionServiceTest extends TestCase
{
    public function test_builds_structured_suggestions_from_conversation_history(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'intent' => 'schedule',
                            'confidence' => 0.91,
                            'summary' => 'Cliente pediu corte com a Ana amanhã à tarde.',
                            'follow_up_question' => null,
                            'suggestions' => [[
                                'service_name' => 'Corte',
                                'professional_name' => 'Ana',
                                'starts_at' => '2026-08-21T14:00:00-03:00',
                                'ends_at' => '2026-08-21T14:45:00-03:00',
                                'duration_minutes' => 45,
                                'confidence' => 0.88,
                                'summary' => 'Horário livre compatível com o pedido.',
                                'missing_information' => [],
                            ]],
                        ], JSON_THROW_ON_ERROR),
                    ],
                ]],
            ], 200),
        ]);

        $service = app(AppointmentSuggestionService::class);

        $result = $service->suggest(
            [
                new ChatMessage('user', 'Oi, quero marcar um corte com a Ana amanhã à tarde'),
                new ChatMessage('assistant', 'Perfeito, a Ana atende corte. Prefere 14h ou 16h?'),
                ['role' => 'user', 'content' => 'Pode ser 14h'],
            ],
            new SuggestionContext(
                tenantName: 'Salão Ana',
                timezone: 'America/Sao_Paulo',
                client: ['name' => 'Maria', 'phone' => '11988887777'],
                services: [['name' => 'Corte', 'duration_minutes' => 45]],
                professionals: [['name' => 'Ana']],
                now: '2026-08-20T10:00:00-03:00',
            ),
        );

        $this->assertSame('schedule', $result->intent);
        $this->assertSame(0.91, $result->confidence);
        $this->assertCount(1, $result->suggestions);
        $this->assertSame('Corte', $result->suggestions[0]->serviceName);
        $this->assertSame('Ana', $result->suggestions[0]->professionalName);
        $this->assertSame('2026-08-21T14:00:00-03:00', $result->suggestions[0]->startsAt);

        Http::assertSent(function ($request): bool {
            $payload = $request->data();

            $this->assertSame('gpt-4o-mini', $payload['model']);
            $this->assertSame(['type' => 'json_object'], $payload['response_format']);
            $this->assertSame('system', $payload['messages'][0]['role']);
            $this->assertStringContainsString('Salão Ana', $payload['messages'][1]['content']);
            $this->assertSame('user', $payload['messages'][2]['role']);
            $this->assertSame('Pode ser 14h', $payload['messages'][4]['content']);

            return str_contains($request->url(), '/chat/completions');
        });
    }

    public function test_rejects_empty_conversation(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        app(AppointmentSuggestionService::class)->suggest(
            [],
            new SuggestionContext(tenantName: 'Salão Ana'),
        );
    }

    public function test_fails_when_openai_returns_an_error(): void
    {
        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response(['error' => 'boom'], 500),
        ]);

        $this->expectException(OpenAIException::class);

        app(AppointmentSuggestionService::class)->suggest(
            [new ChatMessage('user', 'Quero agendar')],
            new SuggestionContext(tenantName: 'Salão Ana'),
        );
    }
}
