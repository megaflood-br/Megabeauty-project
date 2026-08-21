<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Resources\ClientResource\Pages\CreateClient;
use App\Filament\Resources\ClientResource\Pages\EditClient;
use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ClientResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_page_renders_the_belasis_client_sheet(): void
    {
        [$tenant, $user] = $this->authenticateOwner();

        $this->actingAs($user)
            ->get('/admin/'.$tenant->subdomain.'/clients/create')
            ->assertOk()
            ->assertSee('Cadastro')
            ->assertSee('Novo cliente')
            ->assertSee('Celular')
            ->assertSee('Apelido')
            ->assertSee('Configurações')
            ->assertSee('Ativo')
            ->assertSee('Notificações');
    }

    public function test_owner_can_create_a_client_from_the_sheet(): void
    {
        [$tenant] = $this->authenticateOwner();

        Livewire::test(CreateClient::class)
            ->fillForm([
                'name' => 'Rosangela Manzatto',
                'nickname' => 'Rosangela',
                'phone' => '11988887777',
                'email' => 'rosa@example.com',
                'is_active' => true,
                'notifications_enabled' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('clients', [
            'tenant_id' => $tenant->id,
            'name' => 'Rosangela Manzatto',
            'nickname' => 'Rosangela',
            'phone' => '11988887777',
            'is_active' => true,
        ]);
    }

    public function test_edit_page_can_update_client_profile_fields(): void
    {
        [$tenant, $user] = $this->authenticateOwner();

        $client = Client::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Rosangela Manzatto',
            'phone' => '11988887777',
        ]);

        $this->actingAs($user)
            ->get('/admin/'.$tenant->subdomain.'/clients/'.$client->id.'/edit')
            ->assertOk()
            ->assertSee('Rosangela Manzatto')
            ->assertSee('Cadastro')
            ->assertSee('Agendamentos');

        Livewire::test(EditClient::class, ['record' => $client->id])
            ->fillForm([
                'nickname' => 'Rosa',
                'document' => '12345678900',
                'address_city' => 'São Paulo',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'nickname' => 'Rosa',
            'document' => '12345678900',
            'address_city' => 'São Paulo',
        ]);
    }

    /**
     * @return array{0: Tenant, 1: User}
     */
    private function authenticateOwner(): array
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'demo']);
        $user = User::factory()->owner()->create(['tenant_id' => $tenant->id]);

        $this->actingAs($user);
        $this->actingAsTenant($tenant);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Filament::setTenant($tenant);

        return [$tenant, $user];
    }
}
