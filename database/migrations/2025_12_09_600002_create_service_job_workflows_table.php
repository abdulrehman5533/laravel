<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_job_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_job_id')->constrained('service_jobs')->onDelete('cascade');
            $table->integer('step_number');
            $table->enum('workflow_step', ['received', 'checking', 'workshop', 'polishing', 'completed'])->default('received');
            $table->text('description')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('photos')->nullable(); // JSON array
            $table->timestamps();

            $table->index(['service_job_id', 'workflow_step']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_job_workflows');
    }
};
