<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_attendance', function (Blueprint $table) {
            $table->boolean('is_locked')->default(false)->after('late_minutes');
            $table->boolean('is_early_leave')->default(false)->after('is_locked');
            $table->json('metadata')->nullable()->after('is_early_leave');
        });
    }

    public function down(): void
    {
        Schema::table('hr_attendance', function (Blueprint $table) {
            $table->dropColumn(['is_locked', 'is_early_leave', 'metadata']);
        });
    }
};
