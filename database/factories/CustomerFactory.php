<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_code' => 'CUST'.$this->faker->unique()->numberBetween(1000, 9999),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'mobile' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->optional()->date(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'address_line_1' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country' => 'Pakistan',
            'customer_type' => $this->faker->randomElement(['individual', 'business']),
            'credit_limit' => $this->faker->randomFloat(2, 0, 50000),
            'loyalty_points' => $this->faker->numberBetween(0, 5000),
            'membership_level' => $this->faker->randomElement(['bronze', 'silver', 'gold', 'platinum']),
            'is_active' => $this->faker->boolean(95),
            'branch_id' => \App\Models\Branch::factory(),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
