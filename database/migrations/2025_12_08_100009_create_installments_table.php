<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name');
            $table->string('frequency'); // daily, weekly, monthly
            $table->integer('number_of_installments');
            $table->decimal('late_fee_percentage', 5, 2)->default(0);
            $table->integer('skip_allowed')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('set null');
            $table->foreignId('plan_id')->constrained('installment_plans')->onDelete('cascade');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('installment_amount', 12, 2);
            $table->integer('total_installments');
            $table->integer('paid_installments')->default(0);
            $table->integer('skipped_installments')->default(0);
            $table->decimal('outstanding_amount', 12, 2);
            $table->decimal('total_late_fees', 12, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('active'); // active, completed, defaulted
            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });

        Schema::create('installment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_id')->constrained('installments')->onDelete('cascade');
            $table->integer('sequence_number');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->decimal('late_fee', 12, 2)->default(0);
            $table->string('status')->default('pending'); // pending, paid, overdue, skipped
            $table->date('paid_date')->nullable();
            $table->decimal('amount_paid', 12, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['installment_id', 'due_date']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_schedules');
        Schema::dropIfExists('installments');
        Schema::dropIfExists('installment_plans');
    }
};
