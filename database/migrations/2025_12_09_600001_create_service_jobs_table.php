<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained('crm_customers')->onDelete('set null');
            $table->enum('service_type', ['repair', 'cleaning', 'polishing', 'resizing', 'setting', 'stone_replace', 'customization', 'other'])->default('repair');
            $table->text('item_description');
            $table->string('item_type')->nullable();
            $table->decimal('item_weight', 10, 3)->nullable();
            $table->text('issue_description');
            $table->decimal('estimated_charge', 15, 2);
            $table->decimal('final_charge', 15, 2)->nullable();
            $table->enum('status', ['received', 'checking', 'workshop', 'polishing', 'completed', 'delivered', 'rejected'])->default('received');
            $table->integer('current_workflow_step')->default(0);
            $table->timestamp('received_date');
            $table->timestamp('expected_completion_date');
            $table->timestamp('actual_completion_date')->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->text('before_photos')->nullable(); // JSON array of photo paths
            $table->text('after_photos')->nullable(); // JSON array of photo paths
            $table->text('work_notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamp('delivery_reminder_sent')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->text('special_instructions')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('job_number');
            $table->index('status');
            $table->index(['customer_id', 'status']);
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_jobs');
    }
};
