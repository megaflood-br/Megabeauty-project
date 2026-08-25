<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\AiAgent;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class AgentWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_widget_is_available_on_the_local_tenant_path(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'demo', 'name' => 'Salão Demo']);
        $this->actingAsTenant($tenant);

        AiAgent::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Luna',
            'public_slug' => 'luna',
            'greeting' => 'Oi! Eu sou a Luna',
            'is_active' => true,
        ]);

        $this->get('/t/demo/agente/luna')
            ->assertOk()
            ->assertSee('Luna')
            ->assertSee('Oi! Eu sou a Luna');
    }

    public function test_widget_answers_with_catalog_lookup(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'demo']);
        $this->actingAsTenant($tenant);

        AiAgent::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Luna',
            'public_slug' => 'luna',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Shampoo Hidratação 300ml',
            'sku' => 'SHP-300',
            'price' => 62,
        ]);

        Http::fake([
            'https://api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => 'O shampoo custa R$ 62,00.',
                    ],
                ]],
            ], 200),
        ]);

        $this->postJson('/t/demo/agente/luna/mensagens', [
            'message' => 'Quanto custa o shampoo?',
            'session_key' => 'test-session',
        ])->assertOk()
            ->assertJsonPath('reply', 'O shampoo custa R$ 62,00.')
            ->assertJsonPath('agent.name', 'Luna');
    }

    public function test_inactive_agent_is_not_exposed(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'demo']);
        $this->actingAsTenant($tenant);

        AiAgent::factory()->create([
            'tenant_id' => $tenant->id,
            'public_slug' => 'luna',
            'is_active' => false,
        ]);

        $this->get('/t/demo/agente/luna')->assertNotFound();
    }
}
