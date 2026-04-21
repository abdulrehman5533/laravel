<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'code' => $this->faker->unique()->lexify('????'),
            'is_active' => true,
        ];
    }
}
