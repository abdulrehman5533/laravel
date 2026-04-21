<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_status')->default('active')->after('is_active');
            $table->timestamp('last_login_at')->nullable()->after('user_status');
            $table->timestamp('password_changed_at')->nullable()->after('last_login_at');
            $table->boolean('force_password_change')->default(false)->after('password_changed_at');
            $table->integer('failed_login_attempts')->default(0)->after('force_password_change');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            $table->index('user_status');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->integer('session_timeout_minutes')->default(120)->after('is_active');
        });

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('session_id');
            $table->string('device_type')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('login_at')->nullable();
            $table->timestamp('last_activity_at');
            $table->timestamp('terminated_at')->nullable();
            $table->string('termination_reason')->nullable();
            $table->timestamps();
            $table->index('user_id');
            $table->index('session_id');
            $table->unique(['user_id', 'session_id']);
        });

        Schema::create('security_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('category');
            $table->string('subject')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->index();
            $table->index('action');
            $table->index('category');
            $table->index('user_id');
        });

        Schema::create('role_session_timeouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->integer('timeout_minutes');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_session_timeouts');
        Schema::dropIfExists('security_audit_logs');
        Schema::dropIfExists('user_sessions');

        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('session_timeout_minutes');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_status',
                'last_login_at',
                'password_changed_at',
                'force_password_change',
                'failed_login_attempts',
                'locked_until',
            ]);
        });
    }
};
