<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('warehouse_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained();
            $table->string('reconciliation_number')->unique();
            $table->string('status')->default('pending'); // pending, completed, cancelled
            $table->foreignId('conducted_by')->constrained('users');
            $table->timestamp('conducted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('warehouse_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')->constrained('warehouse_reconciliations')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('inventory_products');
            $table->decimal('system_quantity', 15, 4);
            $table->decimal('physical_quantity', 15, 4);
            $table->decimal('discrepancy', 15, 4);
            $table->string('adjustment_action')->nullable(); // none, update_stock, write_off
            $table->boolean('is_adjusted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouse_reconciliation_items');
        Schema::dropIfExists('warehouse_reconciliations');
    }
};
