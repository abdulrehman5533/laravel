<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoldRate>
 */
class GoldRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->date(),
            'gold_24k' => $this->faker->randomFloat(2, 55000, 65000),
            'gold_22k' => $this->faker->randomFloat(2, 50500, 59500),
            'gold_18k' => $this->faker->randomFloat(2, 41250, 48750),
            'silver' => $this->faker->randomFloat(2, 650, 850),
            'platinum' => $this->faker->randomFloat(2, 32000, 42000),
            'currency' => 'PKR',
            'source' => $this->faker->randomElement(['Market Data', 'Bullion Association', 'Local Exchange', 'International Rate']),
            'is_active' => $this->faker->boolean(95), // 95% chance of being active
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => now(),
        ];
    }
}
