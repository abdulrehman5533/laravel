<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceJob>
 */
class ServiceJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_number' => 'SJ-'.$this->faker->unique()->numberBetween(100000, 999999),
            'customer_id' => $this->faker->numberBetween(1, 50),
            'product_id' => $this->faker->numberBetween(1, 100),
            'service_type' => $this->faker->randomElement(['repair', 'maintenance', 'cleaning', 'polishing', 'sizing', 'restoration']),
            'description' => $this->faker->paragraph(),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'urgent']),
            'status' => $this->faker->randomElement(['pending', 'in_progress', 'completed', 'cancelled']),
            'estimated_cost' => $this->faker->randomFloat(2, 100, 5000),
            'actual_cost' => $this->faker->randomFloat(2, 0, 5000),
            'estimated_completion_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'actual_completion_date' => $this->faker->optional()->dateTimeBetween('-30 days', 'now'),
            'assigned_to' => $this->faker->numberBetween(1, 10),
            'notes' => $this->faker->optional()->paragraph(),
            'customer_feedback' => $this->faker->optional()->sentence(),
            'rating' => $this->faker->optional()->numberBetween(1, 5),
            'created_by' => 1, // Default admin user
            'branch_id' => 1, // Default branch
            'created_at' => $this->faker->dateTimeBetween('-90 days', 'now'),
            'updated_at' => now(),
        ];
    }
}
