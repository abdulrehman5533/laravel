<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->onDelete('cascade');
            $table->string('payment_reference')->unique();

            // Payment Details
            $table->date('payment_date');
            $table->decimal('amount_paid', 15, 2);
            $table->enum('payment_method', [
                'Bank_Transfer',
                'Cheque',
                'Cash',
                'Credit_Card',
                'Online_Payment',
                'NEFT',
                'RTGS',
                'UPI',
            ])->default('Bank_Transfer');

            // Bank Details (if applicable)
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();
            $table->enum('cheque_status', ['Pending', 'Cleared', 'Bounced', 'Cancelled'])->default('Pending');
            $table->string('transaction_id')->nullable();
            $table->string('bank_reference')->nullable();

            // Amount Breakdown
            $table->decimal('principal_amount', 15, 2)->default(0);
            $table->decimal('gst_amount', 15, 2)->default(0);
            $table->decimal('discount_applied', 15, 2)->default(0);
            $table->decimal('interest_charged', 15, 2)->default(0);

            // Status & Approval
            $table->enum('status', ['Pending', 'Confirmed', 'Failed', 'Reversed'])->default('Pending');
            $table->boolean('approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // References & Notes
            $table->string('invoice_reference')->nullable();
            $table->text('notes')->nullable();
            $table->text('attachment_path')->nullable();

            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('branch_id');
            $table->index('supplier_id');
            $table->index('purchase_order_id');
            $table->index('payment_date');
            $table->index('payment_method');
            $table->index('status');
            $table->index('cheque_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
