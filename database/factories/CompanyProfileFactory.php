<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CompanyProfile;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyProfile>
 */
class CompanyProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'legal_name' => fake()->company().' LTDA',
            'trade_name' => fake()->company(),
            'cnpj' => '11444777000161',
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('113#######'),
            'whatsapp' => fake()->numerify('119########'),
            'instagram' => '@'.fake()->userName(),
            'address_street' => fake()->streetName(),
            'address_number' => (string) fake()->buildingNumber(),
            'address_neighborhood' => fake()->citySuffix(),
            'address_city' => 'São Paulo',
            'address_state' => 'SP',
            'address_zip' => fake()->numerify('01###000'),
            'about' => 'Salão especializado em cabelo, unhas e estética.',
            'policies' => 'Cancelamentos com até 2 horas de antecedência.',
            'opening_hours' => [
                ['weekday' => 'monday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
                ['weekday' => 'tuesday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
                ['weekday' => 'wednesday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
                ['weekday' => 'thursday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
                ['weekday' => 'friday', 'open' => '08:00', 'close' => '18:00', 'closed' => false],
                ['weekday' => 'saturday', 'open' => '08:00', 'close' => '14:00', 'closed' => false],
                ['weekday' => 'sunday', 'open' => null, 'close' => null, 'closed' => true],
            ],
            'payment_methods' => ['Pix', 'Débito', 'Crédito', 'Dinheiro'],
        ];
    }
}
