<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Filament\Pages\ManageCompanyProfile;
use App\Filament\Resources\AiAgentResource\Pages\CreateAiAgent;
use App\Models\AiAgent;
use App\Models\CompanyProfile;
use App\Models\Tenant;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class AiAgentStudioTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_company_profile_with_cnpj(): void
    {
        [$tenant] = $this->authenticateOwner();

        Livewire::test(ManageCompanyProfile::class)
            ->fillForm([
                'trade_name' => 'Salão Ana',
                'legal_name' => 'Salão Ana LTDA',
                'cnpj' => '11.444.777/0001-61',
                'address_city' => 'Campinas',
                'address_state' => 'SP',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $profile = CompanyProfile::query()->first();

        $this->assertNotNull($profile);
        $this->assertSame($tenant->id, $profile->tenant_id);
        $this->assertSame('11444777000161', $profile->cnpj);
        $this->assertSame('Campinas', $profile->address_city);
    }

    public function test_owner_can_create_an_agent_with_avatar_and_attendance(): void
    {
        $this->authenticateOwner();

        Livewire::test(CreateAiAgent::class)
            ->fillForm([
                'name' => 'Luna',
                'public_slug' => 'luna',
                'role' => 'receptionist',
                'avatar_color' => '#059669',
                'greeting' => 'Oi, eu sou a Luna!',
                'tone' => 'cordial',
                'emoji_usage' => 'few',
                'reply_length' => 'short',
                'attendance_script' => 'Consulte a tabela antes de informar valores.',
                'model' => 'gpt-4o-mini',
                'temperature' => 0.4,
                'max_tokens' => 600,
                'tools' => AiAgent::defaultTools(),
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('ai_agents', [
            'name' => 'Luna',
            'public_slug' => 'luna',
            'is_default' => 1,
            'is_active' => 1,
        ]);
    }

    public function test_agent_pages_are_reachable(): void
    {
        [$tenant, $user] = $this->authenticateOwner();

        AiAgent::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Luna',
            'public_slug' => 'luna',
        ]);

        $this->actingAs($user)
            ->get('/admin/'.$tenant->subdomain.'/ai-agents')
            ->assertOk()
            ->assertSee('Luna');

        $this->actingAs($user)
            ->get('/admin/'.$tenant->subdomain.'/company-profile')
            ->assertOk()
            ->assertSee('CNPJ');

        $this->actingAs($user)
            ->get('/admin/'.$tenant->subdomain.'/products')
            ->assertOk();

        $this->actingAs($user)
            ->get('/admin/'.$tenant->subdomain.'/price-tables')
            ->assertOk();

        $this->actingAs($user)
            ->get('/admin/'.$tenant->subdomain.'/agent-playground')
            ->assertOk()
            ->assertSee('Playground');
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
