<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = DB::select('SHOW TABLES');

        foreach ($tables as $tableRow) {
            $tableArray = (array) $tableRow;
            $table = reset($tableArray);

            if (in_array($table, ['migrations', 'tenants'])) {
                continue;
            }

            if (! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->softDeletes();
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

            if (Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropSoftDeletes();
                });
            }
        }
    }
};
