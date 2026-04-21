<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryProduct>
 */
class InventoryProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => 'PRD-'.$this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->words(3, true),
            'category_id' => \App\Models\ProductCategory::factory(),
            'purity_id' => \App\Models\PurityLevel::factory(),
            'branch_id' => \App\Models\Branch::factory(),
            'weight' => $this->faker->randomFloat(3, 1, 50),
            'cost_price' => $this->faker->randomFloat(2, 500, 5000),
            'selling_price' => $this->faker->randomFloat(2, 600, 6000),
            'current_stock' => $this->faker->numberBetween(1, 100),
            'reorder_level' => 5,
            'reorder_quantity' => 10,
            'status' => 'active',
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
