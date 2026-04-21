<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DashboardPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Inventory
            ['resource' => 'inventory', 'action' => 'view', 'description' => 'View inventory dashboard'],
            ['resource' => 'inventory', 'action' => 'manage', 'description' => 'Manage inventory products'],

            // POS
            ['resource' => 'pos', 'action' => 'view', 'description' => 'View POS dashboard'],
            ['resource' => 'pos', 'action' => 'create_sale', 'description' => 'Create new sales'],

            // CRM
            ['resource' => 'crm', 'action' => 'view', 'description' => 'View CRM dashboard'],

            // Service
            ['resource' => 'service', 'action' => 'view', 'description' => 'View service dashboard'],

            // Calculator
            ['resource' => 'calculator', 'action' => 'view', 'description' => 'Use weight calculator'],

            // Gold Rate
            ['resource' => 'gold_rate', 'action' => 'view', 'description' => 'View gold rates'],
            ['resource' => 'gold_rate', 'action' => 'manage', 'description' => 'Update gold rates'],

            // Purchase
            ['resource' => 'purchase', 'action' => 'view', 'description' => 'View purchase and suppliers'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(
                ['slug' => $perm['resource'].'.'.$perm['action']],
                [
                    'name' => ucfirst($perm['action']).' '.ucfirst($perm['resource']),
                    'resource' => $perm['resource'],
                    'action' => $perm['action'],
                    'description' => $perm['description'],
                ]
            );
        }

        // Assign to Admin
        $admin = Role::where('slug', 'admin')->first();
        if ($admin) {
            $admin->permissions()->syncWithoutDetaching(Permission::all());
        }

        // Assign to Manager (Account Manager)
        $manager = Role::where('slug', 'account_manager')->first();
        if ($manager) {
            $manager->permissions()->syncWithoutDetaching(Permission::whereIn('resource', ['inventory', 'pos', 'crm', 'service', 'calculator', 'gold_rate', 'reports', 'purchase'])->get());
        }

        // Assign to Cashier
        $cashier = Role::where('slug', 'cashier')->first();
        if ($cashier) {
            $cashier->permissions()->syncWithoutDetaching(Permission::whereIn('resource', ['pos', 'calculator', 'gold_rate'])->get());
        }

        // Assign to Accountant
        $accountant = Role::where('slug', 'accountant')->first();
        if ($accountant) {
            $accountant->permissions()->syncWithoutDetaching(Permission::whereIn('resource', ['reports', 'gold_rate', 'calculator'])->get());
        }
    }
}
