<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('jewellery_products')->onDelete('set null');

            // Item Details
            $table->string('item_code');
            $table->string('description');
            $table->enum('material_type', ['Gold', 'Silver', 'Gemstones', 'Diamonds', 'Mixed'])->default('Mixed');
            $table->string('unit')->default('Grams');

            // Quality Specifications
            $table->string('purity')->nullable(); // e.g., 916, 925, etc.
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 15, 4);
            $table->decimal('line_amount', 15, 2);

            // Additional Tracking
            $table->decimal('quantity_received', 12, 4)->default(0);
            $table->decimal('quantity_returned', 12, 4)->default(0);
            $table->decimal('quantity_remaining', 12, 4)->default(0);

            // Quality Control
            $table->enum('quality_status', ['Pending', 'Accepted', 'Rejected', 'Partial'])->default('Pending');
            $table->text('quality_remarks')->nullable();
            $table->decimal('weight_variance', 12, 4)->default(0);
            $table->decimal('purity_variance', 5, 2)->default(0);

            // Batch & Serial Tracking
            $table->string('batch_number')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('specifications')->nullable();

            // Tax
            $table->decimal('gst_percentage', 5, 2)->default(18);
            $table->decimal('gst_amount', 15, 2)->default(0);

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('purchase_order_id');
            $table->index('product_id');
            $table->index('quality_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
