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
        Schema::create('api_sync_queues', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->unique();
            $table->string('device_id')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->json('payload');
            $table->string('status')->default('pending'); // pending, processed, failed
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_sync_queues');
    }
};
