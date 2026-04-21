<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pos_sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_sale_id')->index();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('sku')->nullable()->index();
            $table->string('description')->nullable();
            $table->decimal('quantity', 16, 3)->default(1);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_price', 16, 2)->default(0);
            $table->decimal('line_total', 16, 2)->default(0);
            $table->decimal('making_charge', 16, 2)->default(0);
            $table->decimal('wastage_percent', 8, 3)->default(0);
            $table->decimal('wastage_amount', 16, 2)->default(0);
            $table->decimal('tax_percent', 8, 3)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pos_sale_id')->references('id')->on('pos_sales')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_sale_items');
    }
};
