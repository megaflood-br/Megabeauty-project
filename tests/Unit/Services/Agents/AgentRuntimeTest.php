<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Agents;

use App\Models\AiAgent;
use App\Models\Product;
use App\Models\Service;
use App\Models\Tenant;
use App\Services\Agents\AgentRuntime;
use App\Services\OpenAI\DTOs\ChatMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AgentRuntimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_looks_up_catalog_prices_with_openai_tools_before_replying(): void
    {
        $tenant = Tenant::factory()->create();
        $this->actingAsTenant($tenant);

        $agent = AiAgent::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Luna',
            'public_slug' => 'luna',
            'tools' => AiAgent::defaultTools(),
        ]);

        Product::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Shampoo Hidratação 300ml',
            'sku' => 'SHP-300',
            'price' => 62,
            'is_active' => true,
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::sequence()
                ->push([
                    'choices' => [[
                        'finish_reason' => 'tool_calls',
                        'message' => [
                            'role' => 'assistant',
                            'content' => null,
                            'tool_calls' => [[
                                'id' => 'call_shampoo',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'lookup_products',
                                    'arguments' => '{"query":"shampoo"}',
                                ],
                            ]],
                        ],
                    ]],
                ], 200)
                ->push([
                    'choices' => [[
                        'message' => [
                            'role' => 'assistant',
                            'content' => 'O Shampoo Hidratação 300ml custa R$ 62,00.',
                        ],
                    ]],
                ], 200),
        ]);

        $reply = app(AgentRuntime::class)->reply(
            $agent,
            [new ChatMessage('user', 'Quanto custa o shampoo?')],
        );

        $this->assertSame('O Shampoo Hidratação 300ml custa R$ 62,00.', $reply->content);
        $this->assertSame(2, $reply->rounds);
        $this->assertSame('lookup_products', $reply->toolTraces[0]['name'] ?? null);
        $this->assertSame('1 produto(s) encontrado(s)', $reply->toolTraces[0]['result_summary'] ?? null);

        Http::assertSent(function ($request): bool {
            $payload = $request->data();

            return isset($payload['tools'])
                && collect($payload['tools'])->pluck('function.name')->contains('lookup_products');
        });

        $bodies = [];
        Http::assertSent(function ($request) use (&$bodies): bool {
            $bodies[] = $request->data();

            return true;
        });

        $this->assertNotEmpty($bodies[1]['messages'] ?? []);
        $toolMessage = collect($bodies[1]['messages'])->firstWhere('role', 'tool');
        $this->assertIsArray($toolMessage);
        $this->assertStringContainsString('Shampoo Hidratação 300ml', (string) $toolMessage['content']);
        $this->assertStringContainsString('62', (string) $toolMessage['content']);
    }

    public function test_does_not_dump_the_whole_service_catalog_into_the_system_prompt(): void
    {
        $tenant = Tenant::factory()->create(['name' => 'Salão Demo']);
        $this->actingAsTenant($tenant);

        $agent = AiAgent::factory()->create([
            'tenant_id' => $tenant->id,
            'tools' => [AiAgent::TOOL_LOOKUP_SERVICES],
        ]);

        Service::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Alongamento de Unha Secreto',
            'price' => 180,
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => 'Posso te ajudar com valores. Qual serviço você quer?'],
                ]],
            ], 200),
        ]);

        app(AgentRuntime::class)->reply(
            $agent,
            [new ChatMessage('user', 'Oi')],
        );

        Http::assertSent(function ($request): bool {
            $encoded = json_encode($request->data());

            return is_string($encoded)
                && ! str_contains($encoded, 'Alongamento de Unha Secreto');
        });
    }
}
