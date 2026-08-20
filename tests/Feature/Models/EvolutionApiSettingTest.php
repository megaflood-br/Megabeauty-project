<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\EvolutionApiSetting;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class EvolutionApiSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_is_encrypted_at_rest(): void
    {
        $tenant = Tenant::factory()->create();
        $this->actingAsTenant($tenant);

        $plainToken = 'evo_secret_token_123';

        $setting = EvolutionApiSetting::query()->create([
            'url' => 'https://evolution.example.com',
            'instance_name' => 'salao-ana',
            'token' => $plainToken,
        ]);

        $raw = DB::table('evolution_api_settings')->where('id', $setting->id)->value('token');

        $this->assertNotSame($plainToken, $raw);
        $this->assertSame($plainToken, $setting->fresh()->token);
        $this->assertArrayNotHasKey('token', $setting->toArray());
        $this->assertTrue($setting->isConfigured());
    }

    public function test_settings_are_isolated_per_tenant(): void
    {
        $tenantA = Tenant::factory()->create();
        $tenantB = Tenant::factory()->create();

        $this->actingAsTenant($tenantA);
        EvolutionApiSetting::factory()->create([
            'tenant_id' => $tenantA->id,
            'instance_name' => 'instance-a',
        ]);

        $this->actingAsTenant($tenantB);
        EvolutionApiSetting::factory()->create([
            'tenant_id' => $tenantB->id,
            'instance_name' => 'instance-b',
        ]);

        $this->actingAsTenant($tenantA);
        $this->assertSame('instance-a', EvolutionApiSetting::query()->value('instance_name'));
        $this->assertSame(1, EvolutionApiSetting::query()->count());
    }
}
