<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $dbName = config('database.connections.mysql.database');
        $tables = DB::select('SHOW TABLES');

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
            'telescope_entries',
            'telescope_entries_tags',
            'telescope_monitoring',
        ];

        foreach ($tables as $tableRow) {
            $tableArray = (array) $tableRow;
            $table = reset($tableArray); // Gets the first value regardless of key name

            if (in_array($table, $excludedTables)) {
                continue;
            }

            if (! Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->unsignedBigInteger('tenant_id')->nullable()->after('id')->index();
                    $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = DB::select('SHOW TABLES');

        foreach ($tables as $tableRow) {
            $tableArray = (array) $tableRow;
            $table = reset($tableArray);

            if (Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
};
