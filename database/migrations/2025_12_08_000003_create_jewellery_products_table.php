<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jewellery_products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('material');
            $table->decimal('weight', 10, 3);
            $table->decimal('purity', 5, 2);
            $table->decimal('making_charge', 10, 2)->default(0);
            $table->decimal('stone_weight', 10, 3)->default(0);
            $table->decimal('stone_cost', 10, 2)->default(0);
            $table->decimal('cost_price', 12, 2);
            $table->decimal('selling_price', 12, 2);
            $table->decimal('gold_rate', 10, 2);
            $table->integer('stock')->default(0);
            $table->json('images')->nullable();
            $table->string('hallmark')->nullable();
            $table->string('certificate_no')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jewellery_products');
    }
};
