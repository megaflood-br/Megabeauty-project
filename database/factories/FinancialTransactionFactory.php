<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FinancialTransactionStatus;
use App\Enums\FinancialTransactionType;
use App\Enums\PaymentMethod;
use App\Models\FinancialTransaction;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancialTransaction>
 */
class FinancialTransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'appointment_id' => null,
            'client_id' => null,
            'professional_id' => null,
            'type' => FinancialTransactionType::Income,
            'category' => 'servico',
            'amount' => fake()->randomFloat(2, 30, 400),
            'payment_method' => PaymentMethod::Pix,
            'status' => FinancialTransactionStatus::Paid,
            'paid_at' => now(),
            'description' => fake()->sentence(),
            'reference' => fake()->uuid(),
        ];
    }
}
