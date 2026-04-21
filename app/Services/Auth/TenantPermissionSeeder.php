<?php

namespace App\Services\Auth;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;

class TenantPermissionSeeder
{
    public function seed(Tenant $tenant): Role
    {
        // Set tenant context
        app()->instance('current_tenant_id', $tenant->id);

        $permissions = [
            // Cashbook
            ['resource' => 'cashbook', 'action' => 'view', 'description' => 'View cashbook entries'],
            ['resource' => 'cashbook', 'action' => 'create', 'description' => 'Create new cashbook entries'],
            ['resource' => 'cashbook', 'action' => 'edit', 'description' => 'Edit cashbook entries'],
            ['resource' => 'cashbook', 'action' => 'delete', 'description' => 'Delete cashbook entries'],

            // Expenses
            ['resource' => 'expense', 'action' => 'view', 'description' => 'View expenses'],
            ['resource' => 'expense', 'action' => 'create', 'description' => 'Create expenses'],
            ['resource' => 'expense', 'action' => 'approve', 'description' => 'Approve expenses'],

            // Inventory
            ['resource' => 'inventory', 'action' => 'view', 'description' => 'View inventory'],
            ['resource' => 'inventory', 'action' => 'create', 'description' => 'Create products'],
            ['resource' => 'inventory', 'action' => 'edit', 'description' => 'Edit products'],

            // POS
            ['resource' => 'pos', 'action' => 'view', 'description' => 'Access POS interface'],
            ['resource' => 'pos', 'action' => 'sale', 'description' => 'Create sales'],

            // Girvi
            ['resource' => 'girvi', 'action' => 'view', 'description' => 'View girvi records'],
            ['resource' => 'girvi', 'action' => 'create', 'description' => 'Create new girvi loans'],

            // Admin
            ['resource' => 'users', 'action' => 'manage', 'description' => 'Manage users'],
        ];

        $createdPermissions = [];
        foreach ($permissions as $perm) {
            $createdPermissions[] = Permission::create([
                'tenant_id' => $tenant->id,
                'slug' => $perm['resource'].'.'.$perm['action'],
                'name' => ucfirst($perm['action']).' '.ucfirst($perm['resource']),
                'description' => $perm['description'],
                'resource' => $perm['resource'],
                'action' => $perm['action'],
            ]);
        }

        // Create Administrator Role
        $adminRole = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full system access',
            'is_active' => true,
        ]);

        // Sync all created permissions to admin
        $adminRole->permissions()->sync(collect($createdPermissions)->pluck('id'));

        // Create other default roles (optional)
        $cashierRole = Role::create([
            'tenant_id' => $tenant->id,
            'name' => 'Cashier',
            'slug' => 'cashier',
            'description' => 'POS and basic transactions',
            'is_active' => true,
        ]);

        // Assign permissions to cashier
        $cashierPermissions = collect($createdPermissions)->filter(function ($p) {
            return in_array($p->resource, ['pos', 'cashbook', 'expense']);
        })->pluck('id');
        $cashierRole->permissions()->sync($cashierPermissions);

        return $adminRole;
    }
}
