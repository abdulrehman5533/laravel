<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('total_purchases', 12, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->date('last_transaction_date')->nullable();
            $table->decimal('performance_rating', 3, 2)->default(0);
            $table->string('status')->default('active'); // active, suspended, closed
            $table->timestamps();

            $table->unique(['vendor_id', 'branch_id']);
        });

        Schema::create('supplier_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_ledger_id')->constrained('supplier_ledgers')->onDelete('cascade');
            $table->date('date');
            $table->string('type'); // debit, credit
            $table->decimal('amount', 12, 2);
            $table->string('reference_type'); // purchase, payment, debit_note, credit_note
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('running_balance', 12, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['supplier_ledger_id', 'date']);
        });

        Schema::create('debit_credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_ledger_id')->constrained('supplier_ledgers')->onDelete('cascade');
            $table->string('note_type'); // debit_note, credit_note
            $table->string('reference_number')->unique();
            $table->date('date');
            $table->decimal('amount', 12, 2);
            $table->text('reason');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_credit_notes');
        Schema::dropIfExists('supplier_ledger_entries');
        Schema::dropIfExists('supplier_ledgers');
    }
};
