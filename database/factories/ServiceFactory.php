<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Service;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement(['Corte', 'Coloração', 'Manicure', 'Barba', 'Escova']),
            'description' => fake()->sentence(),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'price' => fake()->randomFloat(2, 40, 350),
            'color' => fake()->hexColor(),
            'category' => fake()->randomElement(['cabelo', 'unhas', 'estetica']),
            'is_active' => true,
        ];
    }
}
