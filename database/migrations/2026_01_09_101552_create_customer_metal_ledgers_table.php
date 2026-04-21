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
        Schema::create('customer_metal_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->date('transaction_date');
            $table->enum('transaction_type', ['sale', 'return', 'payment', 'bhav_cut', 'adjustment']);
            $table->enum('metal_type', ['gold', 'silver']);
            $table->decimal('weight_in', 15, 3)->default(0);
            $table->decimal('weight_out', 15, 3)->default(0);
            $table->decimal('running_weight_balance', 15, 3);
            $table->string('reference_type')->nullable(); // sale, payment, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('rate_at_transaction', 15, 2)->nullable(); // bhav cut rate
            $table->decimal('conversion_amount', 15, 2)->nullable(); // cash value if bhav cut
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['customer_id', 'metal_type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_metal_ledgers');
    }
};
