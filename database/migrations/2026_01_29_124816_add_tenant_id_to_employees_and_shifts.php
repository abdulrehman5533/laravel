<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->index()->constrained()->onDelete('cascade');
            }
        });

        Schema::table('hr_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_shifts', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->index()->constrained()->onDelete('cascade');
            }
        });
        
        // Also check hr_employee_shifts
        Schema::table('hr_employee_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_employee_shifts', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->index()->constrained()->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });

        Schema::table('hr_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('hr_shifts', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });

        Schema::table('hr_employee_shifts', function (Blueprint $table) {
            if (Schema::hasColumn('hr_employee_shifts', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });
    }
};
