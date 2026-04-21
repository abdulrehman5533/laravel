<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Create a default admin tenant
        $tenant = Tenant::updateOrCreate(
            ['subdomain' => 'admin'],
            [
                'name' => 'System Administrator',
                'is_active' => true,
                'plan_id' => 1, // Enterprise
                'settings' => ['theme' => 'dark'],
            ]
        );

        // Assign all existing records to this tenant
        $tables = Schema::getTables();
        $excludedTables = [
            'tenants',
            'migrations',
            'jobs',
            'failed_jobs',
            'cache',
            'sessions',
            'personal_access_tokens',
            'password_reset_tokens',
            'cache_locks',
        ];

        foreach ($tables as $tableInfo) {
            $table = $tableInfo['name'];
            if (in_array($table, $excludedTables)) {
                continue;
            }

            if (Schema::hasColumn($table, 'tenant_id')) {
                DB::table($table)->whereNull('tenant_id')->update(['tenant_id' => $tenant->id]);
            }
        }
    }
}
