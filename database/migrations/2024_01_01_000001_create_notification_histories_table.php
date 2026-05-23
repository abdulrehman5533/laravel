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
        Schema::create('notification_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->enum('type', ['email', 'sms', 'in-app', 'whatsapp', 'push'])->default('in-app');
            $table->string('recipient')->nullable(); // email, phone, user_id
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('title')->nullable();
            $table->string('action_url')->nullable();
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->boolean('is_read')->default(false);
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('type');
            $table->index('status');
            $table->index('is_read');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_histories');
    }
};
