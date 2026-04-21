<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('return_number')->unique();

            // Return Details
            $table->date('return_date');
            $table->enum('return_reason', [
                'Quality_Defect',
                'Quantity_Variance',
                'Purity_Issue',
                'Damaged',
                'Not_As_Ordered',
                'Excess_Stock',
                'Pricing_Error',
                'Other',
            ])->default('Other');
            $table->text('return_reason_details')->nullable();

            // Financial Details
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('gst_percentage', 5, 2)->default(18);
            $table->decimal('gst_amount', 15, 2)->default(0);
            $table->decimal('return_amount', 15, 2)->default(0);

            // Refund & Credit
            $table->enum('refund_type', ['Credit_Note', 'Cash_Refund', 'Adjustment'])->default('Credit_Note');
            $table->date('refund_date')->nullable();
            $table->boolean('refund_processed')->default(false);
            $table->decimal('refund_amount', 15, 2)->default(0);

            // Status
            $table->enum('status', ['Initiated', 'Approved', 'Dispatched', 'Received', 'Processed', 'Rejected'])->default('Initiated');
            $table->boolean('approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            // Documentation
            $table->text('notes')->nullable();
            $table->string('reference_number')->nullable();

            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('branch_id');
            $table->index('purchase_order_id');
            $table->index('supplier_id');
            $table->index('return_date');
            $table->index('status');
            $table->index('refund_processed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_returns');
    }
};
