<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_authenticated_owner_can_open_the_tenant_dashboard(): void
    {
        $tenant = Tenant::factory()->create(['subdomain' => 'demo']);
        $user = User::factory()->owner()->create([
            'tenant_id' => $tenant->id,
            'email' => 'owner@megabeauty.test',
        ]);

        $this->actingAs($user)
            ->get('/admin/'.$tenant->subdomain)
            ->assertOk();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect();
    }
}
