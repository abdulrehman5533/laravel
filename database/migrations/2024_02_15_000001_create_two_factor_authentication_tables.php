<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Two-Factor Authentication Settings
        Schema::create('two_factor_authentications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('secret_key');
            $table->json('backup_codes')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->enum('method', ['totp', 'sms', 'email'])->default('totp');
            $table->string('phone_number')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });

        // 2FA Verification Logs
        Schema::create('two_factor_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('method');
            $table->enum('status', ['success', 'failed'])->default('failed');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('two_factor_verification_logs');
        Schema::dropIfExists('two_factor_authentications');
    }
};
