<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branch = Branch::first();

        if (! $branch) {
            $branch = Branch::create([
                'name' => 'Main Branch',
                'code' => 'MAIN',
                'is_active' => true,
            ]);
        }

        $accounts = [
            [
                'branch_id' => $branch->id,
                'account_name' => 'Business Primary Account',
                'account_number' => '001122334455',
                'bank_name' => 'HBL Bank',
                'ifsc_code' => 'HBL0001',
                'account_type' => 'Current',
                'opening_balance' => 5000000,
                'current_balance' => 5000000,
                'is_active' => true,
            ],
            [
                'branch_id' => $branch->id,
                'account_name' => 'Operational Savings',
                'account_number' => '998877665544',
                'bank_name' => 'Meezan Bank',
                'ifsc_code' => 'MEZ0002',
                'account_type' => 'Savings',
                'opening_balance' => 2000000,
                'current_balance' => 2000000,
                'is_active' => true,
            ],
            [
                'branch_id' => $branch->id,
                'account_name' => 'Director Trust Account',
                'account_number' => '554433221100',
                'bank_name' => 'Standard Chartered',
                'ifsc_code' => 'SCB0003',
                'account_type' => 'Current',
                'opening_balance' => 10000000,
                'current_balance' => 10000000,
                'is_active' => true,
            ],
        ];

        foreach ($accounts as $account) {
            BankAccount::firstOrCreate(
                ['account_number' => $account['account_number']],
                $account
            );
        }
    }
}
