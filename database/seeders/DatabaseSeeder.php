<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\GoldRate;
use App\Models\InventoryProduct;
use App\Models\ProductCategory;
use App\Models\PurityLevel;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create Branch
        $branch = Branch::firstOrCreate(['id' => 1], [
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'is_active' => true,
        ]);

        // Run roles and permissions seeder first
        $this->call([
            RolesAndPermissionsSeeder::class,
            InventorySeeder::class,
        ]);

        // Create admin user
        $user = User::firstOrCreate(
            ['email' => 'sariapratab@gmail.com'],
            [
                'name' => 'MAGIA LUPOS Admin',
                'password' => Hash::make('Sicl@3241'),
                'phone' => '+919876543210',
                'user_type' => 'Adminisrator',
                'is_active' => true,
                'branch_id' => $branch->id,
            ]
        );

        // Assign admin role to user
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $user->update(['role_id' => $adminRole->id]);
        }

        // Authenticate the user so audit logs can capture the user_id
        Auth::login($user);

        // Set initial gold rate
        GoldRate::firstOrCreate(
            ['date' => today()],
            [
                'rate_22k' => 5500,
                'rate_18k' => 4500,
                'rate_24k' => 6000,
                'silver_rate' => 75,
            ]
        );

        // Create sample categories
        $categories = [
            ['name' => 'Ring', 'code' => 'RNG'],
            ['name' => 'Necklace', 'code' => 'NEC'],
            ['name' => 'Earring', 'code' => 'EAR'],
            ['name' => 'Bracelet', 'code' => 'BRA'],
            ['name' => 'Pendant', 'code' => 'PEN'],
            ['name' => 'Bangle', 'code' => 'BAN'],
            ['name' => 'Chain', 'code' => 'CHN'],
            ['name' => 'Anklet', 'code' => 'ANK'],
            ['name' => 'Set', 'code' => 'SET'],
            ['name' => 'Other', 'code' => 'OTH'],
        ];

        foreach ($categories as $cat) {
            ProductCategory::firstOrCreate(
                ['name' => $cat['name']],
                $cat + ['is_active' => true]
            );
        }

        // Create sample purities
        $purities = [
            ['name' => '22K Gold', 'karat' => 22, 'percentage' => 91.6],
            ['name' => '18K Gold', 'karat' => 18, 'percentage' => 75.0],
            ['name' => '925 Silver', 'karat' => 925, 'percentage' => 92.5],
        ];

        foreach ($purities as $purity) {
            PurityLevel::firstOrCreate(
                ['name' => $purity['name']],
                $purity + ['is_active' => true]
            );
        }

        // Create sample customers
        $customers = [
            [
                'customer_code' => 'CUST0001',
                'name' => 'Rajesh Kumar',
                'first_name' => 'Rajesh',
                'last_name' => 'Kumar',
                'email' => 'rajesh@gmail.com',
                'phone' => '+919876543211',
                'mobile' => '+919876543211',
                'address_line_1' => '123 Main Street, Mumbai',
                'total_purchases' => 125000,
                'customer_type' => 'individual',
                'branch_id' => $branch->id,
                'is_active' => true,
            ],
            [
                'customer_code' => 'CUST0002',
                'name' => 'Priya Sharma',
                'first_name' => 'Priya',
                'last_name' => 'Sharma',
                'email' => 'priya@gmail.com',
                'phone' => '+919876543212',
                'mobile' => '+919876543212',
                'address_line_1' => '456 Park Avenue, Delhi',
                'total_purchases' => 85000,
                'customer_type' => 'individual',
                'branch_id' => $branch->id,
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::firstOrCreate(
                ['email' => $customer['email']],
                $customer
            );
        }

        // Create sample vendors
        $vendors = [
            [
                'vendor_code' => 'VEN0001',
                'name' => 'Gold Suppliers Inc.',
                'contact_person' => 'Mr. Gupta',
                'email' => 'info@goldsuppliers.com',
                'phone' => '+911234567890',
                'address' => 'Gold Market, Zaveri Bazaar, Mumbai',
                'gst_number' => '27ABCDE1234F1Z5',
                'material_speciality' => 'Gold',
                'total_purchases' => 5000000,
                'is_active' => true,
            ],
        ];

        foreach ($vendors as $vendor) {
            Vendor::firstOrCreate(
                ['vendor_code' => $vendor['vendor_code']],
                $vendor
            );
        }

        // Create sample products
        $ringCat = ProductCategory::where('name', 'Ring')->first();
        $gold22k = PurityLevel::where('name', '22K Gold')->first();
        $user = User::where('email', 'sariapratab@gmail.com')->first();

        $ring = InventoryProduct::firstOrCreate(
            ['sku' => 'RING001'],
            [
                'name' => 'Gold Wedding Ring',
                'description' => '22K Gold Wedding Ring with traditional design',
                'category_id' => $ringCat->id,
                'purity_id' => $gold22k->id,
                'weight' => 8.5,
                'cost_price' => 45000,
                'selling_price' => 52000,
                'current_stock' => 5,
                'reorder_level' => 1,
                'reorder_quantity' => 2,
                'status' => 'active',
                'branch_id' => $branch->id,
                'created_by' => $user->id,
            ]
        );

        // Create sample sales
        $rajesh = Customer::where('email', 'rajesh@gmail.com')->first();
        $priya = Customer::where('email', 'priya@gmail.com')->first();

        \App\Models\PosSale::firstOrCreate(
            ['invoice_no' => 'INV-20260101-ABC123'],
            [
                'branch_id' => $branch->id,
                'pos_customer_id' => $rajesh->id,
                'created_by' => $user->id,
                'sale_time' => now()->subDays(2),
                'subtotal' => 52000,
                'total' => 52000,
                'status' => 'completed',
                'payment_status' => 'paid',
                'stock_moved' => true,
            ]
        );

        \App\Models\PosSale::firstOrCreate(
            ['invoice_no' => 'INV-20260102-XYZ789'],
            [
                'branch_id' => $branch->id,
                'pos_customer_id' => $priya->id,
                'created_by' => $user->id,
                'sale_time' => now()->subDay(),
                'subtotal' => 104000,
                'total' => 104000,
                'status' => 'held',
                'payment_status' => 'unpaid',
                'stock_moved' => false,
            ]
        );

        \App\Models\PosSale::firstOrCreate(
            ['invoice_no' => 'INV-20260103-WALK01'],
            [
                'branch_id' => $branch->id,
                'pos_customer_id' => null,
                'created_by' => $user->id,
                'sale_time' => now(),
                'subtotal' => 25000,
                'total' => 25000,
                'status' => 'open',
                'payment_status' => 'unpaid',
                'stock_moved' => false,
            ]
        );

        // Add a sample image entry for the product
        \App\Models\InventoryProductImage::firstOrCreate(
            ['product_id' => $ring->id],
            [
                'image_path' => 'products/xtqoYmy6dW55e4lIxdjUu9WNRtAUlkh8eFTOLKUX.jpg',
                'is_primary' => true,
                'sort_order' => 0,
            ]
        );

        // Call other seeders
        $this->call([
            AccountsSeeder::class,
            POSSeeder::class,
        ]);

        $this->command->info('Database seeded successfully!');

        $this->command->info('Login credentials:');
        $this->command->info('Email: sariapratab@gmail.com');
        $this->command->info('Password: Sicl@3241');
    }
}
