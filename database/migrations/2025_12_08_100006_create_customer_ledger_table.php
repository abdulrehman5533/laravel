<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->integer('grace_period_days')->default(30);
            $table->decimal('late_fee_percentage', 5, 2)->default(0);
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->date('last_transaction_date')->nullable();
            $table->string('status')->default('active'); // active, suspended, closed
            $table->timestamps();

            $table->unique(['customer_id', 'branch_id']);
        });

        Schema::create('customer_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_ledger_id')->constrained('customer_ledgers')->onDelete('cascade');
            $table->date('date');
            $table->string('type'); // debit, credit
            $table->decimal('amount', 12, 2);
            $table->string('reference_type'); // sale, payment, adjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('running_balance', 12, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['customer_ledger_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_ledger_entries');
        Schema::dropIfExists('customer_ledgers');
    }
};
