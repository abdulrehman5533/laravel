<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\PosPricingTier;
use Illuminate\Database\Seeder;

class POSSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample customers
        Customer::firstOrCreate(
            ['customer_code' => 'CUST-RAJ'],
            [
                'first_name' => 'Raj',
                'last_name' => 'Patel',
                'mobile' => '+1-555-0101',
                'email' => 'raj@example.com',
                'customer_type' => 'vip',
                'loyalty_points' => 5500,
                'credit_limit' => 10000,
                'is_active' => true,
                'branch_id' => 1,
            ]
        );

        Customer::firstOrCreate(
            ['customer_code' => 'CUST-PRIYA'],
            [
                'first_name' => 'Priya',
                'last_name' => 'Singh',
                'mobile' => '+1-555-0102',
                'email' => 'priya@example.com',
                'customer_type' => 'loyalty',
                'loyalty_points' => 2300,
                'credit_limit' => 5000,
                'is_active' => true,
                'branch_id' => 1,
            ]
        );

        Customer::firstOrCreate(
            ['customer_code' => 'CUST-JOHN'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'mobile' => '+1-555-0103',
                'email' => 'john@example.com',
                'customer_type' => 'regular',
                'loyalty_points' => 0,
                'credit_limit' => 0,
                'is_active' => true,
                'branch_id' => 1,
            ]
        );

        Customer::firstOrCreate(
            ['customer_code' => 'CUST-SARAH'],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'mobile' => '+1-555-0104',
                'email' => 'sarah@example.com',
                'customer_type' => 'vip',
                'loyalty_points' => 8200,
                'credit_limit' => 15000,
                'is_active' => true,
                'branch_id' => 1,
            ]
        );

        // Create pricing tiers
        PosPricingTier::firstOrCreate(
            ['name' => 'Wholesale Bulk'],
            [
                'discount_percent' => 15.00,
                'conditions' => ['min_qty' => 100],
            ]
        );

        PosPricingTier::firstOrCreate(
            ['name' => 'Wholesale Standard'],
            [
                'discount_percent' => 10.00,
                'conditions' => ['min_qty' => 50],
            ]
        );

        PosPricingTier::firstOrCreate(
            ['name' => 'VIP Premium'],
            [
                'discount_percent' => 20.00,
                'conditions' => ['customer_type' => 'vip'],
            ]
        );

        PosPricingTier::firstOrCreate(
            ['name' => 'Loyalty Program'],
            [
                'discount_percent' => 5.00,
                'conditions' => ['customer_type' => 'loyalty'],
            ]
        );
    }
}
