<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        $admin = Role::firstOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Administrator',
                'description' => 'Full system access',
                'is_active' => true,
            ]
        );

        $accountManager = Role::firstOrCreate(
            ['slug' => 'account_manager'],
            [
                'name' => 'Account Manager',
                'description' => 'Manage all accounts and finance operations',
                'is_active' => true,
            ]
        );

        $accountant = Role::firstOrCreate(
            ['slug' => 'accountant'],
            [
                'name' => 'Accountant',
                'description' => 'View and enter financial transactions',
                'is_active' => true,
            ]
        );

        $cashier = Role::firstOrCreate(
            ['slug' => 'cashier'],
            [
                'name' => 'Cashier',
                'description' => 'Manage cash and basic transactions',
                'is_active' => true,
            ]
        );

        $viewer = Role::firstOrCreate(
            ['slug' => 'viewer'],
            [
                'name' => 'Viewer',
                'description' => 'View-only access to financial reports',
                'is_active' => true,
            ]
        );

        // Create permissions for Cashbook
        $permissions = [
            // Cashbook
            ['resource' => 'cashbook', 'action' => 'view', 'description' => 'View cashbook entries'],
            ['resource' => 'cashbook', 'action' => 'create', 'description' => 'Create new cashbook entries'],
            ['resource' => 'cashbook', 'action' => 'edit', 'description' => 'Edit cashbook entries'],
            ['resource' => 'cashbook', 'action' => 'delete', 'description' => 'Delete cashbook entries'],
            ['resource' => 'cashbook', 'action' => 'export', 'description' => 'Export cashbook reports'],
            ['resource' => 'cashbook', 'action' => 'manage_shifts', 'description' => 'Manage cashier shifts'],

            // Expenses
            ['resource' => 'expense', 'action' => 'view', 'description' => 'View expenses'],
            ['resource' => 'expense', 'action' => 'create', 'description' => 'Create expenses'],
            ['resource' => 'expense', 'action' => 'edit', 'description' => 'Edit expenses'],
            ['resource' => 'expense', 'action' => 'delete', 'description' => 'Delete expenses'],
            ['resource' => 'expense', 'action' => 'approve', 'description' => 'Approve expenses'],
            ['resource' => 'expense', 'action' => 'export', 'description' => 'Export expenses'],

            // Customer Ledger
            ['resource' => 'customer_ledger', 'action' => 'view', 'description' => 'View customer ledger'],
            ['resource' => 'customer_ledger', 'action' => 'create', 'description' => 'Create customer ledger entries'],
            ['resource' => 'customer_ledger', 'action' => 'edit', 'description' => 'Edit customer ledger'],
            ['resource' => 'customer_ledger', 'action' => 'export', 'description' => 'Export customer ledger'],

            // Supplier Ledger
            ['resource' => 'supplier_ledger', 'action' => 'view', 'description' => 'View supplier ledger'],
            ['resource' => 'supplier_ledger', 'action' => 'create', 'description' => 'Create supplier ledger entries'],
            ['resource' => 'supplier_ledger', 'action' => 'edit', 'description' => 'Edit supplier ledger'],
            ['resource' => 'supplier_ledger', 'action' => 'export', 'description' => 'Export supplier ledger'],

            // Installments
            ['resource' => 'installment', 'action' => 'view', 'description' => 'View installments'],
            ['resource' => 'installment', 'action' => 'create', 'description' => 'Create installments'],
            ['resource' => 'installment', 'action' => 'edit', 'description' => 'Edit installments'],
            ['resource' => 'installment', 'action' => 'record_payment', 'description' => 'Record installment payments'],
            ['resource' => 'installment', 'action' => 'export', 'description' => 'Export installments'],

            // Bank
            ['resource' => 'bank', 'action' => 'view', 'description' => 'View bank accounts and transactions'],
            ['resource' => 'bank', 'action' => 'create', 'description' => 'Create bank accounts'],
            ['resource' => 'bank', 'action' => 'edit', 'description' => 'Edit bank accounts'],
            ['resource' => 'bank', 'action' => 'reconcile', 'description' => 'Reconcile bank statements'],

            // Reports
            ['resource' => 'reports', 'action' => 'view', 'description' => 'View financial reports'],
            ['resource' => 'reports', 'action' => 'export', 'description' => 'Export financial reports'],
            ['resource' => 'reports', 'action' => 'print', 'description' => 'Print financial reports'],

            // Audit Log
            ['resource' => 'audit_log', 'action' => 'view', 'description' => 'View audit logs'],
            ['resource' => 'audit_log', 'action' => 'export', 'description' => 'Export audit logs'],

            // Girvi
            ['resource' => 'girvi', 'action' => 'view', 'description' => 'View girvi records'],
            ['resource' => 'girvi', 'action' => 'create', 'description' => 'Create new girvi loans'],
            ['resource' => 'girvi', 'action' => 'edit', 'description' => 'Edit girvi records'],
            ['resource' => 'girvi', 'action' => 'delete', 'description' => 'Delete girvi records'],
            ['resource' => 'girvi', 'action' => 'post_interest', 'description' => 'Manually post interest'],
            ['resource' => 'girvi', 'action' => 'record_payment', 'description' => 'Record girvi payments'],
            ['resource' => 'girvi', 'action' => 'manage_reminders', 'description' => 'Trigger reminders'],

            // Admin
            ['resource' => 'roles', 'action' => 'manage', 'description' => 'Manage roles and permissions'],
            ['resource' => 'users', 'action' => 'manage', 'description' => 'Manage users'],

            // Security Manager
            ['resource' => 'security_manager', 'action' => 'view', 'description' => 'View security manager dashboard'],
            ['resource' => 'security_manager', 'action' => 'create', 'description' => 'Create security settings'],
            ['resource' => 'security_manager', 'action' => 'update', 'description' => 'Update security settings'],
            ['resource' => 'security_manager', 'action' => 'delete', 'description' => 'Delete security logs/settings'],

            // POS & Sales
            ['resource' => 'pos', 'action' => 'view', 'description' => 'View POS interface'],
            ['resource' => 'pos', 'action' => 'create_sale', 'description' => 'Create sales'],
            ['resource' => 'pos', 'action' => 'return', 'description' => 'Process returns'],

            // CRM
            ['resource' => 'crm', 'action' => 'view', 'description' => 'View CRM dashboard'],
            ['resource' => 'crm', 'action' => 'manage_customers', 'description' => 'Manage customers'],
        ];

        foreach ($permissions as $perm) {
            Permission::createFromAction(
                $perm['resource'],
                $perm['action'],
                $perm['description']
            );
        }

        // Get all permissions
        $allPermissions = Permission::all();
        $accountBookPermissions = Permission::whereIn('resource', ['cashbook', 'expense', 'bank'])->get();
        $ledgerPermissions = Permission::whereIn('resource', ['customer_ledger', 'supplier_ledger'])->get();
        $reportPermissions = Permission::whereIn('resource', ['reports', 'audit_log'])->get();

        // Assign permissions to roles
        // Admin - All permissions
        $admin->permissions()->sync($allPermissions);

        // Account Manager - Most permissions except role/user management
        $accountManagerPerms = $allPermissions->whereNotIn('resource', ['roles', 'users'])->pluck('id');
        $accountManager->permissions()->sync($accountManagerPerms);

        // Accountant - Ledger, reports, and basic transactions
        $accountantPerms = $ledgerPermissions
            ->merge($reportPermissions)
            ->merge(Permission::where('resource', 'expense')->where('action', '!=', 'approve')->get())
            ->merge(Permission::where('resource', 'installment')->where('action', '!=', 'delete')->get())
            ->pluck('id')
            ->unique();
        $accountant->permissions()->sync($accountantPerms);

        // Cashier - Only cashbook and basic expenses
        $cashierPerms = Permission::whereIn('resource', ['cashbook', 'bank', 'pos', 'crm'])
            ->where('action', '!=', 'delete')
            ->pluck('id');
        $cashier->permissions()->sync($cashierPerms);

        // Viewer - Only read permissions
        $viewerPerms = $allPermissions->filter(fn ($p) => $p->action === 'view')->pluck('id');
        $viewer->permissions()->sync($viewerPerms);
    }
}
