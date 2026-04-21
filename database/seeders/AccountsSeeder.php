<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ExpenseCategorySeeder::class,
            InstallmentPlanSeeder::class,
            ChartOfAccountSeeder::class,
        ]);
    }
}
