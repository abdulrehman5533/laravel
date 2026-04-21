<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PosSale>
 */
class PosSaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_no' => 'INV-'.$this->faker->unique()->numberBetween(100000, 999999),
            'branch_id' => \App\Models\Branch::factory(),
            'pos_customer_id' => \App\Models\Customer::factory(),
            'created_by' => \App\Models\User::factory(),
            'sale_time' => now(),
            'subtotal' => $this->faker->randomFloat(2, 100, 5000),
            'making_charges' => $this->faker->randomFloat(2, 0, 500),
            'wastage_amount' => $this->faker->randomFloat(2, 0, 200),
            'tax_amount' => $this->faker->randomFloat(2, 0, 500),
            'discount' => $this->faker->randomFloat(2, 0, 200),
            'total' => $this->faker->randomFloat(2, 100, 5500),
            'status' => 'open',
            'stock_moved' => false,
            'currency' => 'PKR',
            'exchange_rate' => 1.0,
            'is_wholesale' => false,
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => now(),
        ];
    }
}
