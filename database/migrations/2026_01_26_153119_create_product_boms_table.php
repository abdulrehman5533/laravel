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
        Schema::create('product_boms', function (Blueprint $table) {
            $table->id();
            $table->string('bom_number')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // Design/Product reference
            $table->unsignedBigInteger('inventory_product_id')->nullable()->comment('Target product template');

            // Metals
            $table->decimal('expected_gross_weight', 12, 4)->default(0);
            $table->decimal('expected_net_weight', 12, 4)->default(0);
            $table->decimal('allowed_wastage_percentage', 5, 2)->default(0);

            // Purity requirements
            $table->unsignedBigInteger('purity_id');

            // Labor
            $table->decimal('estimated_labor_cost', 15, 2)->default(0);
            $table->enum('labor_type', ['per_gram', 'fixed', 'per_piece'])->default('per_gram');

            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('inventory_product_id')->references('id')->on('inventory_products');
            $table->foreign('purity_id')->references('id')->on('purity_levels');
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('product_bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_bom_id')->constrained()->onDelete('cascade');

            $table->enum('type', ['metal', 'stone', 'finding', 'other']);
            $table->string('item_name');
            $table->string('item_description')->nullable();

            // Specifications
            $table->decimal('quantity', 12, 4)->default(1);
            $table->string('unit')->default('pcs'); // pcs, carats, grams
            $table->decimal('weight', 12, 4)->default(0);

            // For stones
            $table->string('stone_type')->nullable(); // Diamond, Ruby, etc.
            $table->string('stone_shape')->nullable();
            $table->string('stone_color')->nullable();
            $table->string('stone_clarity')->nullable();
            $table->string('stone_cut')->nullable();

            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_bom_items');
        Schema::dropIfExists('product_boms');
    }
};
