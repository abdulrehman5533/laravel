<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Budgets
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->nullable()->constrained('tenants')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('budget_period', ['monthly', 'quarterly', 'yearly'])->default('yearly');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'approved', 'active', 'closed'])->default('draft');
            $table->decimal('total_budget', 15, 2)->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('tenant_id');
            $table->index('branch_id');
            $table->index('status');
        });

        // Budget Items
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade');
            $table->foreignId('chart_of_account_id')->nullable();
            $table->string('category');
            $table->text('description')->nullable();
            $table->decimal('budgeted_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->decimal('variance', 15, 2)->default(0);
            $table->decimal('variance_percentage', 5, 2)->default(0);
            $table->enum('status', ['on_track', 'warning', 'exceeded'])->default('on_track');
            $table->timestamps();
            $table->softDeletes();
            $table->index('budget_id');
            $table->index('status');
        });

        // Budget Approvals
        Schema::create('budget_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('approval_level');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->index('budget_id');
            $table->index('status');
        });

        // Budget Tracking
        Schema::create('budget_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->onDelete('cascade');
            $table->foreignId('budget_item_id')->constrained('budget_items')->onDelete('cascade');
            $table->date('period_date');
            $table->decimal('budgeted_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2);
            $table->decimal('variance', 15, 2);
            $table->decimal('variance_percentage', 5, 2);
            $table->timestamp('created_at')->nullable();
            $table->index('budget_id');
            $table->index('period_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_tracking');
        Schema::dropIfExists('budget_approvals');
        Schema::dropIfExists('budget_items');
        Schema::dropIfExists('budgets');
    }
};
