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
        // 1. Stock Alerts for Re-ordering
        Schema::create('inventory_stock_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('inventory_products')->onDelete('cascade');
            $table->enum('severity', ['warning', 'critical'])->default('warning');
            $table->string('message');
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamp('snoozed_until')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'is_acknowledged']);
        });

        // 2. Multi-Branch Transfers
        Schema::create('inventory_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number')->unique();
            $table->foreignId('from_branch_id')->constrained('branches');
            $table->foreignId('to_branch_id')->constrained('branches');
            $table->enum('status', ['requested', 'approved', 'dispatched', 'received', 'cancelled'])->default('requested');
            $table->text('notes')->nullable();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->foreignId('dispatched_by')->nullable()->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        // 3. Transfer Items
        Schema::create('inventory_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('inventory_transfers')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('inventory_products');
            $table->decimal('quantity', 15, 4);
            $table->timestamps();
        });

        // 4. AI Stock Intelligence Metadata (Extension Table)
        Schema::create('inventory_product_intelligence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique()->constrained('inventory_products')->onDelete('cascade');
            $table->decimal('risk_score', 5, 2)->default(0);
            $table->string('movement_speed')->nullable(); // fast, medium, slow, dead
            $table->date('last_sold_at')->nullable();
            $table->json('ai_suggestions')->nullable();
            $table->timestamp('last_analyzed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_product_intelligence');
        Schema::dropIfExists('inventory_transfer_items');
        Schema::dropIfExists('inventory_transfers');
        Schema::dropIfExists('inventory_stock_alerts');
    }
};
