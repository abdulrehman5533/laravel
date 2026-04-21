<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => \App\Models\ExpenseCategory::factory(),
            'amount' => $this->faker->randomFloat(2, 100, 10000),
            'description' => $this->faker->sentence(),
            'date' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'payment_method' => $this->faker->randomElement(['cash', 'bank_transfer', 'cheque', 'card']),
            'status' => $this->faker->randomElement(['pending', 'approved', 'rejected']),
            'approved_by' => null, // Will be set in test
            'approved_at' => null,
            'rejection_reason' => null,
            'created_by' => \App\Models\User::factory(),
            'branch_id' => \App\Models\Branch::factory(),
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => now(),
        ];
    }
}
