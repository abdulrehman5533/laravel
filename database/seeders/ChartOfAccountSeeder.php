<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class ChartOfAccountSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if ($user) {
            Auth::login($user);
        }

        $accounts = [
            // ASSETS
            ['code' => '1000', 'name' => 'Current Assets', 'type' => 'Asset', 'category' => 'Control', 'key' => 'assets_current'],
            ['code' => '1010', 'name' => 'Cash in Hand', 'type' => 'Asset', 'category' => 'Cash', 'key' => 'cash_in_hand', 'parent' => '1000'],
            ['code' => '1020', 'name' => 'Main Bank Account', 'type' => 'Asset', 'category' => 'Bank', 'key' => 'bank_main', 'parent' => '1000'],
            ['code' => '1030', 'name' => 'Card/POS Terminal', 'type' => 'Asset', 'category' => 'Bank', 'key' => 'bank_card', 'parent' => '1000'],
            ['code' => '1040', 'name' => 'Petty Cash', 'type' => 'Asset', 'category' => 'Cash', 'key' => 'cash_petty', 'parent' => '1000'],
            ['code' => '1210', 'name' => 'Accounts Receivable', 'type' => 'Asset', 'category' => 'Receivable', 'key' => 'ar_customers', 'parent' => '1000'],
            ['code' => '1310', 'name' => 'Inventory - Gold', 'type' => 'Asset', 'category' => 'Inventory', 'key' => 'inv_gold', 'parent' => '1000'],
            ['code' => '1320', 'name' => 'Inventory - Diamond', 'type' => 'Asset', 'category' => 'Inventory', 'key' => 'inv_diamond', 'parent' => '1000'],
            ['code' => '1330', 'name' => 'Inventory - URD (Old Gold)', 'type' => 'Asset', 'category' => 'Inventory', 'key' => 'inv_urd', 'parent' => '1000'],
            ['code' => '1410', 'name' => 'GST Input Tax', 'type' => 'Asset', 'category' => 'Tax', 'key' => 'tax_gst_input', 'parent' => '1000'],

            // LIABILITIES
            ['code' => '2000', 'name' => 'Current Liabilities', 'type' => 'Liability', 'category' => 'Control', 'key' => 'liabilities_current'],
            ['code' => '2010', 'name' => 'Accounts Payable', 'type' => 'Liability', 'category' => 'Payable', 'key' => 'ap_suppliers', 'parent' => '2000'],
            ['code' => '2110', 'name' => 'GST Payable', 'type' => 'Liability', 'category' => 'Tax', 'key' => 'tax_gst_payable', 'parent' => '2000'],

            // REVENUE
            ['code' => '4000', 'name' => 'Operating Revenue', 'type' => 'Revenue', 'category' => 'Control', 'key' => 'revenue_operating'],
            ['code' => '4100', 'name' => 'Gold Sales', 'type' => 'Revenue', 'category' => 'Sales', 'key' => 'sales_gold', 'parent' => '4000'],
            ['code' => '4200', 'name' => 'Making Charges', 'type' => 'Revenue', 'category' => 'Service', 'key' => 'income_making_charges', 'parent' => '4000'],
            ['code' => '4400', 'name' => 'URD Purchase Discount', 'type' => 'Revenue', 'category' => 'Other Income', 'key' => 'income_urd_discount', 'parent' => '4000'],

            // EXPENSES
            ['code' => '5000', 'name' => 'Operating Expenses', 'type' => 'Expense', 'category' => 'Control', 'key' => 'expenses_operating'],
            ['code' => '5010', 'name' => 'Cost of Goods Sold (Gold)', 'type' => 'Expense', 'category' => 'COGS', 'key' => 'cogs_gold', 'parent' => '5000'],
            ['code' => '5410', 'name' => 'Loss on Wastage', 'type' => 'Expense', 'category' => 'Expense', 'key' => 'expense_wastage', 'parent' => '5000'],
            ['code' => '5420', 'name' => 'Karigar Labor/Making Charges', 'type' => 'Expense', 'category' => 'Expense', 'key' => 'expense_labor', 'parent' => '5000'],
            ['code' => '5510', 'name' => 'Discounts Allowed', 'type' => 'Expense', 'category' => 'Expense', 'key' => 'expense_discount', 'parent' => '5000'],
        ];

        // First pass: Create all accounts without parents to avoid dependency issues
        foreach ($accounts as $data) {
            ChartOfAccount::updateOrCreate(
                ['account_code' => $data['code']],
                [
                    'account_name' => $data['name'],
                    'account_type' => $data['type'],
                    'account_category' => $data['category'],
                    'mapping_key' => $data['key'],
                    'is_active' => true,
                ]
            );
        }

        // Second pass: Set parents
        foreach ($accounts as $data) {
            if (isset($data['parent'])) {
                $account = ChartOfAccount::where('account_code', $data['code'])->first();
                $parent = ChartOfAccount::where('account_code', $data['parent'])->first();
                if ($account && $parent) {
                    $account->update(['parent_account_id' => $parent->id]);
                }
            }
        }
    }
}
