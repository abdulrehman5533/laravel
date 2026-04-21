<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PurityLevelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'karat' => $this->faker->numberBetween(10, 24),
            'percentage' => $this->faker->randomFloat(2, 50, 100),
            'is_active' => true,
        ];
    }
}
