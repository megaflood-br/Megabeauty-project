<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TenantStatus;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->company();
        $subdomain = Str::slug($name).'-'.fake()->unique()->numerify('###');

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'subdomain' => $subdomain,
            'custom_domain' => null,
            'status' => TenantStatus::Active,
            'owner_email' => fake()->unique()->companyEmail(),
            'phone' => fake()->numerify('119########'),
            'timezone' => 'America/Sao_Paulo',
            'locale' => 'pt_BR',
            'trial_ends_at' => now()->addDays(14),
            'settings' => [],
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => TenantStatus::Suspended,
        ]);
    }

    public function trial(): static
    {
        return $this->state(fn (): array => [
            'status' => TenantStatus::Trial,
        ]);
    }
}
