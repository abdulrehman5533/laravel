<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Enhance workflow_steps
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->string('name');
            $table->integer('step_order');
            $table->string('approver_type'); // role, user, department, direct_manager
            $table->unsignedBigInteger('approver_id')->nullable(); // role_id, user_id, or department_id
            $table->string('condition_type')->default('none'); // none, amount_greater_than, category_is
            $table->string('condition_value')->nullable();
            $table->integer('sla_hours')->default(24);
            $table->boolean('can_edit_model')->default(false);
            $table->timestamps();
        });

        // Enhance workflow_approvals
        Schema::table('workflow_approvals', function (Blueprint $table) {
            $table->foreignId('step_id')->nullable()->after('workflow_id')->constrained('workflow_steps');
            $table->foreignId('actioned_by')->nullable()->after('approver_id')->constrained('users');
            $table->timestamp('actioned_at')->nullable()->after('actioned_by');
            $table->text('rejection_reason')->nullable()->after('comments');
            $table->json('audit_trail')->nullable()->after('rejection_reason');
        });

        // Add parent_id to tenants for multi-company
        Schema::table('tenants', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('tenants')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });

        Schema::table('workflow_approvals', function (Blueprint $table) {
            $table->dropForeign(['step_id']);
            $table->dropForeign(['actioned_by']);
            $table->dropColumn(['step_id', 'actioned_by', 'actioned_at', 'rejection_reason', 'audit_trail']);
        });

        Schema::dropIfExists('workflow_steps');
    }
};
