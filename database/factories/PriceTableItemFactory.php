<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PriceTable;
use App\Models\PriceTableItem;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceTableItem>
 */
class PriceTableItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'price_table_id' => PriceTable::factory(),
            'name' => fake()->randomElement(['Combo Corte + Escova', 'Combo Manicure + Pedicure', 'Pacote Hidratação']),
            'sku' => strtoupper(fake()->bothify('CMB-##')),
            'unit' => 'sessão',
            'price' => fake()->randomFloat(2, 80, 280),
            'notes' => null,
            'sort_order' => 0,
        ];
    }
}
