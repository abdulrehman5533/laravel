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
        Schema::create('weight_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');

            // Input parameters
            $table->decimal('input_weight', 10, 4);
            $table->string('input_unit'); // g, tola, ratti, carat, oz
            $table->integer('karat_value'); // 24, 22, 18, 14, etc.
            $table->decimal('rate_per_gram', 15, 2);

            // Wastage
            $table->string('wastage_type'); // percentage or fixed
            $table->decimal('wastage_value', 10, 4);

            // Making charges
            $table->string('making_charge_type'); // per_gram, per_piece, percentage
            $table->decimal('making_charge_value', 15, 2);

            // Stones
            $table->decimal('stone_weight', 10, 4)->nullable();
            $table->string('stone_unit')->nullable(); // ratti, carat, g
            $table->decimal('stone_price_per_carat', 15, 2)->nullable();

            // Taxes and discounts
            $table->decimal('tax_percentage', 5, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('custom_charges', 15, 2)->nullable();

            // Configuration
            $table->string('ratti_type')->default('sunari'); // sunari or pakki

            // Results
            $table->json('calculation_details');
            $table->decimal('final_price', 15, 2);

            // Product integration
            $table->boolean('is_saved_as_product')->default(false);
            $table->foreignId('product_id')->nullable()->constrained('inventory_products')->onDelete('set null');

            // Notes
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('branch_id');
            $table->index('karat_value');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_calculations');
    }
};
