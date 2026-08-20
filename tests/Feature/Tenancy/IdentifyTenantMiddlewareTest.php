<?php

declare(strict_types=1);

namespace Tests\Feature\Tenancy;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IdentifyTenantMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_central_host_does_not_require_a_tenant(): void
    {
        $this->getJson('/tenant-context')
            ->assertOk()
            ->assertJson(['tenant' => null]);
    }

    public function test_identifies_tenant_from_subdomain(): void
    {
        $tenant = Tenant::factory()->create([
            'subdomain' => 'salao-ana',
            'name' => 'Salão Ana',
        ]);

        $this->getJson('http://salao-ana.localhost/tenant-context')
            ->assertOk()
            ->assertJson([
                'tenant' => [
                    'id' => $tenant->id,
                    'subdomain' => 'salao-ana',
                    'name' => 'Salão Ana',
                ],
            ]);
    }

    public function test_identifies_tenant_from_custom_domain(): void
    {
        $tenant = Tenant::factory()->create([
            'subdomain' => 'studio-bella',
            'custom_domain' => 'agendamento.studiobella.com.br',
        ]);

        $this->getJson('http://agendamento.studiobella.com.br/tenant-context')
            ->assertOk()
            ->assertJsonPath('tenant.id', $tenant->id)
            ->assertJsonPath('tenant.subdomain', 'studio-bella');
    }

    public function test_unknown_subdomain_returns_not_found(): void
    {
        $this->getJson('http://inexistente.localhost/tenant-context')
            ->assertNotFound();
    }

    public function test_reserved_subdomain_is_treated_as_central(): void
    {
        $this->getJson('http://www.localhost/tenant-context')
            ->assertOk()
            ->assertJson(['tenant' => null]);
    }

    public function test_suspended_tenant_is_forbidden(): void
    {
        Tenant::factory()->suspended()->create([
            'subdomain' => 'pausado',
            'status' => TenantStatus::Suspended,
        ]);

        $this->getJson('http://pausado.localhost/tenant-context')
            ->assertForbidden();
    }

    public function test_api_routes_also_resolve_the_tenant(): void
    {
        Tenant::factory()->create([
            'subdomain' => 'barbearia-joao',
        ]);

        $this->getJson('http://barbearia-joao.localhost/api/ping')
            ->assertOk()
            ->assertJson([
                'pong' => true,
                'tenant' => 'barbearia-joao',
            ]);
    }
}
