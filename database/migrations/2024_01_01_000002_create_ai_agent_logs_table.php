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
        Schema::create('ai_agent_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->string('intent')->nullable();
            $table->text('response')->nullable();
            $table->string('language')->default('ur');
            $table->float('processing_time')->nullable(); // in milliseconds
            $table->enum('status', ['success', 'error', 'pending'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('intent');
            $table->index('status');
            $table->index('language');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_agent_logs');
    }
};
