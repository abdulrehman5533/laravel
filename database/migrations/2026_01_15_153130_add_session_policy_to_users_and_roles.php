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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('idle_timeout_minutes')->nullable()->after('session_timeout_minutes');
            $table->integer('minimize_timeout_minutes')->nullable()->after('idle_timeout_minutes');
            $table->integer('background_timeout_minutes')->nullable()->after('minimize_timeout_minutes');
            $table->boolean('enable_idle_logout')->default(true)->after('background_timeout_minutes');
            $table->boolean('enable_minimize_logout')->default(false)->after('enable_idle_logout');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->integer('idle_timeout_minutes')->default(60)->after('session_timeout_minutes');
            $table->integer('minimize_timeout_minutes')->default(30)->after('idle_timeout_minutes');
            $table->integer('background_timeout_minutes')->default(15)->after('minimize_timeout_minutes');
            $table->boolean('enable_idle_logout')->default(true)->after('background_timeout_minutes');
            $table->boolean('enable_minimize_logout')->default(false)->after('enable_idle_logout');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'idle_timeout_minutes',
                'minimize_timeout_minutes',
                'background_timeout_minutes',
                'enable_idle_logout',
                'enable_minimize_logout',
            ]);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn([
                'idle_timeout_minutes',
                'minimize_timeout_minutes',
                'background_timeout_minutes',
                'enable_idle_logout',
                'enable_minimize_logout',
            ]);
        });
    }
};
