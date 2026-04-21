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
        Schema::table('hr_attendance', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_attendance', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->index()->constrained()->onDelete('cascade');
            }
        });

        Schema::table('hr_payroll', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_payroll', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->index()->constrained()->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_attendance', function (Blueprint $table) {
            if (Schema::hasColumn('hr_attendance', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });

        Schema::table('hr_payroll', function (Blueprint $table) {
            if (Schema::hasColumn('hr_payroll', 'tenant_id')) {
                $table->dropForeign(['tenant_id']);
                $table->dropColumn('tenant_id');
            }
        });
    }
};
