<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_credit_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');

            // Credit Configuration
            $table->decimal('approved_credit_limit', 15, 2);
            $table->decimal('current_credit_used', 15, 2)->default(0);
            $table->decimal('available_credit', 15, 2);

            // Credit Terms
            $table->integer('credit_days')->default(30);
            $table->enum('credit_type', ['Fixed', 'Flexible', 'Special'])->default('Fixed');

            // Overdue Management
            $table->decimal('overdue_amount', 15, 2)->default(0);
            $table->integer('days_overdue')->default(0);
            $table->boolean('has_overdue')->default(false);

            // Credit Status
            $table->enum('status', ['Active', 'Suspended', 'Blocked'])->default('Active');
            $table->date('effective_from');
            $table->date('effective_to')->nullable();

            // Auto-Calculation Tracking
            $table->integer('calculation_frequency_days')->default(30);
            $table->timestamp('last_calculated_at')->nullable();

            // Approval & Authorization
            $table->boolean('requires_approval')->default(true);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes
            $table->index('supplier_id');
            $table->index('branch_id');
            $table->index('status');
            $table->index('has_overdue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_credit_limits');
    }
};
