<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('expense_categories')->onDelete('cascade');
            $table->foreignId('subcategory_id')->nullable()->constrained('expense_subcategories')->onDelete('set null');
            $table->date('date');
            $table->string('vendor_name')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->default('cash');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('description')->nullable();
            $table->decimal('monthly_limit')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_type')->nullable(); // daily, weekly, monthly, yearly
            $table->date('recurrence_start_date')->nullable();
            $table->date('recurrence_end_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'date']);
            $table->index(['category_id', 'date']);
        });

        Schema::create('expense_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained('expenses')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_type'); // bill, invoice, receipt
            $table->string('original_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_attachments');
        Schema::dropIfExists('expenses');
    }
};
