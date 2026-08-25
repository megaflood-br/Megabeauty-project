<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PriceTable;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceTable>
 */
class PriceTableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement(['Tabela de Combos', 'Tabela Verão', 'Tabela Profissional']),
            'description' => fake()->sentence(),
            'valid_from' => now()->startOfMonth()->toDateString(),
            'valid_until' => now()->endOfYear()->toDateString(),
            'is_active' => true,
        ];
    }
}
