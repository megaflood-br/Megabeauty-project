<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('119########'),
            'document' => fake()->numerify('###########'),
            'notes' => null,
            'birth_date' => fake()->optional()->date(),
            'source' => fake()->randomElement(['whatsapp', 'walk_in', 'instagram', 'indicacao']),
        ];
    }
}
