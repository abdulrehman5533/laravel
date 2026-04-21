<?php

namespace Database\Seeders;

use App\Models\InstallmentPlan;
use Illuminate\Database\Seeder;

class InstallmentPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'plan_name' => '3 Months Daily',
                'frequency' => 'daily',
                'number_of_installments' => 90,
                'late_fee_percentage' => 2,
                'skip_allowed' => 2,
                'is_active' => true,
            ],
            [
                'plan_name' => '3 Months Weekly',
                'frequency' => 'weekly',
                'number_of_installments' => 12,
                'late_fee_percentage' => 1.5,
                'skip_allowed' => 1,
                'is_active' => true,
            ],
            [
                'plan_name' => '6 Months Monthly',
                'frequency' => 'monthly',
                'number_of_installments' => 6,
                'late_fee_percentage' => 1,
                'skip_allowed' => 1,
                'is_active' => true,
            ],
            [
                'plan_name' => '12 Months Monthly',
                'frequency' => 'monthly',
                'number_of_installments' => 12,
                'late_fee_percentage' => 0.75,
                'skip_allowed' => 2,
                'is_active' => true,
            ],
            [
                'plan_name' => 'Annual Plan',
                'frequency' => 'yearly',
                'number_of_installments' => 1,
                'late_fee_percentage' => 5,
                'skip_allowed' => 0,
                'is_active' => true,
            ],
        ];

        foreach ($plans as $plan) {
            InstallmentPlan::create($plan);
        }
    }
}
