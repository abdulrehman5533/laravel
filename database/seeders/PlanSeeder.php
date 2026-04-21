<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'description' => 'For small jewelry shops',
            'price' => 2999,
            'max_users' => 3,
            'max_branches' => 1,
            'max_products' => 1000,
            'features' => ['POS', 'Inventory', 'Basic Reporting'],
        ]);

        Plan::create([
            'name' => 'Professional',
            'slug' => 'professional',
            'description' => 'For growing businesses',
            'price' => 7999,
            'max_users' => 10,
            'max_branches' => 3,
            'max_products' => 5000,
            'features' => ['POS', 'Inventory', 'CRM', 'Accounting', 'Advanced Reporting'],
        ]);

        Plan::create([
            'name' => 'Enterprise',
            'slug' => 'enterprise',
            'description' => 'Full-featured ERP for large chains',
            'price' => 19999,
            'max_users' => -1,
            'max_branches' => -1,
            'max_products' => -1,
            'features' => ['All Features', 'AI Analytics', 'Multi-Location', 'Priority Support'],
        ]);
    }
}
