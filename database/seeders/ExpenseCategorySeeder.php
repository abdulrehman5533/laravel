<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\ExpenseSubcategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Workshop & Craftsmanship' => ['Making Charges', 'Stone Setting', 'Polishing & Finishing', 'Repairing Cost', 'Refining Charges'],
            'Labour & Staff' => ['Staff Salary', 'Overtime', 'Incentives & Bonus', 'Employee Benefits', 'Worker Wages'],
            'Marketing & Sales' => ['Social Media Ads', 'Print Media', 'Exhibition Expenses', 'Promotional Gifts', 'Photography'],
            'Inventory & Procurement' => ['Gold Purchase', 'Silver Purchase', 'Stone/Diamond Purchase', 'Consumables', 'Tooling'],
            'Operating Expenses' => ['Electricity Bill', 'Water Bill', 'Internet/Telephone', 'Shop Rent', 'Office Supplies'],
            'Security & Insurance' => ['Security Guard Salary', 'CCTV Maintenance', 'Locker/Vault Rental', 'Jewellery Insurance', 'Shop Insurance'],
            'Logistics & Transport' => ['Fuel', 'Vehicle Maintenance', 'Courier & Shipping', 'Travel Allowance'],
            'Maintenance & Utilities' => ['General Maintenance', 'Cleaning Services', 'AC Servicing', 'Software Subscription'],
            'Taxes & Legal' => ['GST/Sales Tax', 'Professional Tax', 'Income Tax', 'Legal Consulting', 'Audit Fees'],
            'Miscellaneous' => ['Refreshments', 'Charity/Donations', 'Petty Cash Items', 'Other Expenses'],
        ];

        foreach ($categories as $categoryName => $subcategories) {
            $category = ExpenseCategory::firstOrCreate([
                'name' => $categoryName,
            ], [
                'is_active' => true,
            ]);

            foreach ($subcategories as $subcategoryName) {
                ExpenseSubcategory::firstOrCreate([
                    'category_id' => $category->id,
                    'name' => $subcategoryName,
                ], [
                    'is_active' => true,
                ]);
            }
        }
    }
}
