<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->string('name');
            $table->integer('role_id'); // Assuming role IDs are integers
            $table->integer('step_order')->default(0);
            $table->integer('sla_hours')->default(24);
            $table->boolean('can_edit')->default(false);
            $table->timestamps();
        });

        // Add SLA fields to WorkflowApproval
        Schema::table('workflow_approvals', function (Blueprint $table) {
            $table->timestamp('sla_due_at')->nullable();
            $table->boolean('is_escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('workflow_approvals', function (Blueprint $table) {
            $table->dropColumn(['sla_due_at', 'is_escalated', 'escalated_at']);
        });
        Schema::dropIfExists('hr_workflow_steps');
    }
};
