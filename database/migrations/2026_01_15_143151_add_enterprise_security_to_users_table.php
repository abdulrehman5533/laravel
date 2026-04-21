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
            $table->integer('session_timeout_minutes')->nullable()->after('locked_until');
            $table->time('allowed_login_start')->nullable()->after('session_timeout_minutes');
            $table->time('allowed_login_end')->nullable()->after('allowed_login_start');
            $table->json('allowed_days')->nullable()->after('allowed_login_end');
            $table->date('authorized_until')->nullable()->after('allowed_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'session_timeout_minutes',
                'allowed_login_start',
                'allowed_login_end',
                'allowed_days',
                'authorized_until',
            ]);
        });
    }
};
