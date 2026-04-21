<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'po_number' => 'PO-'.$this->faker->unique()->numberBetween(100000, 999999),
            'supplier_id' => $this->faker->numberBetween(1, 10),
            'order_date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'expected_delivery_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'status' => $this->faker->randomElement(['draft', 'pending_approval', 'approved', 'ordered', 'partially_received', 'received', 'cancelled']),
            'total_amount' => $this->faker->randomFloat(2, 1000, 100000),
            'tax_amount' => $this->faker->randomFloat(2, 0, 5000),
            'discount_amount' => $this->faker->randomFloat(2, 0, 1000),
            'shipping_cost' => $this->faker->randomFloat(2, 0, 2000),
            'grand_total' => $this->faker->randomFloat(2, 1000, 110000),
            'payment_terms' => $this->faker->randomElement(['net_15', 'net_30', 'net_45', 'net_60']),
            'payment_status' => $this->faker->randomElement(['unpaid', 'partial', 'paid']),
            'notes' => $this->faker->optional()->paragraph(),
            'approved_by' => null, // Will be set in test
            'approved_at' => null,
            'created_by' => 1, // Default admin user
            'branch_id' => 1, // Default branch
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => now(),
        ];
    }
}
