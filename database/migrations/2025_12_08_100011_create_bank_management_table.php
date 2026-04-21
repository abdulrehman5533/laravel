<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('account_name');
            $table->string('account_number')->unique();
            $table->string('bank_name');
            $table->string('ifsc_code')->nullable();
            $table->string('account_type'); // Savings, Current, etc.
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['branch_id']);
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->date('transaction_date');
            $table->string('transaction_type'); // deposit, withdrawal, transfer
            $table->string('reference_type')->nullable(); // sale, purchase, expense, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('status')->default('posted'); // pending, posted, reconciled
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['bank_account_id', 'transaction_date']);
        });

        Schema::create('bank_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->date('statement_date');
            $table->decimal('opening_balance', 12, 2);
            $table->decimal('closing_balance', 12, 2);
            $table->decimal('total_deposits', 12, 2)->default(0);
            $table->decimal('total_withdrawals', 12, 2)->default(0);
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending'); // pending, reconciled
            $table->timestamps();

            $table->unique(['bank_account_id', 'statement_date']);
        });

        Schema::create('cheque_management', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->string('cheque_number')->unique();
            $table->string('cheque_type'); // issued, received
            $table->decimal('amount', 12, 2);
            $table->date('cheque_date');
            $table->string('payee_name')->nullable();
            $table->string('payer_name')->nullable();
            $table->string('status')->default('issued'); // issued, cleared, bounced, post_dated, cancelled
            $table->date('clearing_date')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['bank_account_id', 'cheque_type']);
            $table->index(['status']);
        });

        Schema::create('digital_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_gateway'); // JazzCash, Easypaisa, Bank QR, POS
            $table->string('transaction_id')->unique();
            $table->date('transaction_date');
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('success'); // success, pending, failed, refunded
            $table->string('reference_type')->nullable(); // sale, purchase, expense
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('customer_identifier')->nullable(); // Phone, Account, etc.
            $table->text('gateway_response')->nullable();
            $table->timestamps();

            $table->index(['transaction_date']);
            $table->index(['payment_gateway', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_payments');
        Schema::dropIfExists('cheque_management');
        Schema::dropIfExists('bank_statements');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');
    }
};
