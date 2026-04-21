<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_attendance', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->constrained('hr_shifts');
            $table->integer('overtime_minutes')->default(0);
            $table->boolean('is_late')->default(false);
            $table->integer('late_minutes')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('hr_attendance', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn(['shift_id', 'overtime_minutes', 'is_late', 'late_minutes']);
        });
    }
};
