<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Evolution;

use App\Models\EvolutionApiSetting;
use App\Models\Tenant;
use App\Services\Evolution\EvolutionApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class EvolutionApiServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sends_a_text_message_to_the_tenant_instance(): void
    {
        $tenant = Tenant::factory()->create();
        $this->actingAsTenant($tenant);

        EvolutionApiSetting::factory()->create([
            'tenant_id' => $tenant->id,
            'url' => 'https://evolution.example.com',
            'instance_name' => 'salao-demo',
            'token' => 'evo-token',
            'is_active' => true,
        ]);

        Http::fake([
            'https://evolution.example.com/message/sendText/salao-demo' => Http::response(['key' => ['id' => '1']], 200),
        ]);

        $result = app(EvolutionApiService::class)->sendText('11988887777', 'Olá, horário confirmado.');

        $this->assertSame('1', data_get($result, 'key.id'));

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), '/message/sendText/salao-demo')
                && $request['number'] === '5511988887777'
                && $request['text'] === 'Olá, horário confirmado.'
                && $request->hasHeader('apikey', 'evo-token');
        });
    }
}
