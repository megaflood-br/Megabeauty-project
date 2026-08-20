<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::factory()->create([
            'name' => 'Salão Demo',
            'subdomain' => 'demo',
            'owner_email' => 'demo@megabeauty.test',
        ]);

        app(TenantContext::class)->run($tenant, function () use ($tenant): void {
            User::factory()->owner()->create([
                'tenant_id' => $tenant->id,
                'name' => 'Demo Owner',
                'email' => 'owner@megabeauty.test',
                'role' => UserRole::Owner,
            ]);
        });
    }
}
