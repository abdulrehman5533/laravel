<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PosSaleItem>
 */
class PosSaleItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pos_sale_id' => 1,
            'sku' => 'SKU-'.$this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->words(3, true),
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit' => 'pcs',
            'unit_price' => $this->faker->randomFloat(2, 100, 1000),
            'making_charge' => $this->faker->randomFloat(2, 10, 100),
            'wastage_amount' => $this->faker->randomFloat(2, 5, 50),
            'tax_amount' => $this->faker->randomFloat(2, 5, 50),
            'line_total' => $this->faker->randomFloat(2, 120, 1200),
        ];
    }
}
