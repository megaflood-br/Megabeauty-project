<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EvolutionApiSetting;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EvolutionApiSetting>
 */
class EvolutionApiSettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'url' => 'https://evolution.example.com',
            'instance_name' => 'salon-'.fake()->unique()->slug(2),
            'token' => 'evo_'.fake()->sha256(),
            'webhook_url' => 'https://app.example.com/webhooks/evolution',
            'is_active' => true,
            'meta' => [],
        ];
    }
}
