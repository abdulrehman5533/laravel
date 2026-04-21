<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('jewellery_products')->onDelete('set null');

            // Price Details
            $table->string('item_code');
            $table->string('item_name');
            $table->enum('material_type', ['Gold', 'Silver', 'Gemstones', 'Diamonds', 'Mixed'])->default('Mixed');
            $table->decimal('unit_price', 15, 4);
            $table->string('unit')->default('Grams');

            // Date Range
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->decimal('price_change_percentage', 5, 2)->default(0);

            // Reference
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->onDelete('set null');
            $table->string('reference_number')->nullable();

            // Analysis
            $table->boolean('is_current_price')->default(false);
            $table->string('purity')->nullable();

            // Audit
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index('supplier_id');
            $table->index('product_id');
            $table->index('effective_from');
            $table->index('is_current_price');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_price_histories');
    }
};
