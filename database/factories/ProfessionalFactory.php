<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Professional;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Professional>
 */
class ProfessionalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('119########'),
            'bio' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'commission_rate' => fake()->randomFloat(2, 0, 50),
            'is_active' => true,
            'working_hours' => [
                'monday' => ['09:00', '18:00'],
                'tuesday' => ['09:00', '18:00'],
                'wednesday' => ['09:00', '18:00'],
                'thursday' => ['09:00', '18:00'],
                'friday' => ['09:00', '18:00'],
                'saturday' => ['09:00', '14:00'],
                'sunday' => ['09:00', '18:00'],
            ],
        ];
    }
}
