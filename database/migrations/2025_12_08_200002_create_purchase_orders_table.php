<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->string('po_number')->unique();
            $table->date('po_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();

            // Order Details
            $table->enum('material_type', ['Gold', 'Silver', 'Gemstones', 'Diamonds', 'Mixed'])->default('Mixed');
            $table->text('description')->nullable();

            // Financial Details
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('gst_percentage', 5, 2)->default(18);
            $table->decimal('gst_amount', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('other_charges', 15, 2)->default(0);
            $table->text('other_charges_description')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);

            // Payment Tracking
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('amount_due', 15, 2)->default(0);

            // Status Tracking
            $table->enum('status', ['Draft', 'Confirmed', 'Dispatched', 'Received', 'Partially_Received', 'Cancelled'])->default('Draft');
            $table->enum('payment_status', ['Unpaid', 'Partial', 'Paid', 'Overdue'])->default('Unpaid');
            $table->boolean('is_overdue')->default(false);
            $table->integer('days_overdue')->default(0);

            // Receiving & Quality Control
            $table->boolean('received')->default(false);
            $table->date('received_date')->nullable();
            $table->decimal('quantity_received', 12, 4)->default(0);
            $table->decimal('quantity_variance', 12, 4)->default(0);
            $table->text('quality_remarks')->nullable();
            $table->enum('quality_status', ['Pending', 'Accepted', 'Rejected', 'Partial'])->default('Pending');

            // References
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms_conditions')->nullable();

            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('branch_id');
            $table->index('supplier_id');
            $table->index('po_date');
            $table->index('status');
            $table->index('payment_status');
            $table->index('is_overdue');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
