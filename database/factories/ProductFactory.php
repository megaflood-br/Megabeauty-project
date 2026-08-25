<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->randomElement([
                'Shampoo Hidratação 300ml',
                'Máscara Capilar 250g',
                'Óleo de Argan 30ml',
                'Esmalte Gel Nude',
                'Leave-in Reconstrutor',
            ]),
            'sku' => strtoupper(fake()->unique()->bothify('PRD-###??')),
            'brand' => fake()->randomElement(['L\'Oréal', 'Wella', 'Risqué', 'Salon Line']),
            'category' => fake()->randomElement(['cabelo', 'unhas', 'finalizadores']),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 18, 180),
            'promotional_price' => null,
            'unit' => 'un',
            'stock_quantity' => fake()->numberBetween(0, 40),
            'is_active' => true,
        ];
    }
}
