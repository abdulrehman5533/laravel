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
        Schema::table('hr_attendance_rules', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_attendance_rules', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade')->after('rule_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_attendance_rules', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
