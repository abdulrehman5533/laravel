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
            if (!Schema::hasColumn('hr_attendance_rules', 'weekly_off_days')) {
                $table->json('weekly_off_days')->nullable()->after('is_default');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hr_attendance_rules', function (Blueprint $table) {
            $table->dropColumn('weekly_off_days');
        });
    }
};
