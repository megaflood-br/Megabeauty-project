<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Models\Client;
use App\Models\Tenant;
use App\Tenancy\Exceptions\MissingTenantException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_queries_without_tenant_context_return_no_rows(): void
    {
        $tenant = Tenant::factory()->create();

        Client::withoutTenant()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Ana Souza',
            'phone' => '11999999999',
        ]);

        $this->assertSame(0, Client::query()->count());
        $this->assertSame(1, Client::withoutTenant()->count());
    }

    public function test_creating_a_model_without_tenant_context_fails(): void
    {
        $this->expectException(MissingTenantException::class);

        Client::query()->create([
            'name' => 'Ana Souza',
            'phone' => '11999999999',
        ]);
    }

    public function test_tenant_scope_hides_records_from_other_tenants(): void
    {
        $tenantA = Tenant::factory()->create(['subdomain' => 'salao-a']);
        $tenantB = Tenant::factory()->create(['subdomain' => 'salao-b']);

        $this->actingAsTenant($tenantA);
        Client::factory()->create([
            'tenant_id' => $tenantA->id,
            'name' => 'Cliente A',
        ]);

        $this->actingAsTenant($tenantB);
        Client::factory()->create([
            'tenant_id' => $tenantB->id,
            'name' => 'Cliente B',
        ]);

        $this->actingAsTenant($tenantA);
        $visible = Client::query()->pluck('name')->all();

        $this->assertSame(['Cliente A'], $visible);

        $this->actingAsTenant($tenantB);
        $this->assertSame(['Cliente B'], Client::query()->pluck('name')->all());

        $this->assertSame(2, Client::withoutTenant()->count());
    }

    public function test_creating_inside_tenant_context_assigns_tenant_id(): void
    {
        $tenant = Tenant::factory()->create();
        $this->actingAsTenant($tenant);

        $client = Client::query()->create([
            'name' => 'Maria Lima',
            'phone' => '11988887777',
        ]);

        $this->assertSame($tenant->id, $client->tenant_id);
        $this->assertTrue($client->is(Client::query()->first()));
    }
}
