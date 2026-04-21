<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_products', function (Blueprint $table) {
            $table->decimal('wholesale_price', 15, 2)->nullable()->after('selling_price');
            $table->decimal('wholesale_margin_percentage', 5, 2)->nullable()->after('wholesale_price');
            $table->json('price_slabs')->nullable()->after('wholesale_margin_percentage'); // [{qty: 10, discount: 5}, {qty: 50, discount: 10}]
            $table->boolean('use_margin_pricing')->default(false)->after('price_slabs');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_products', function (Blueprint $table) {
            $table->dropColumn(['wholesale_price', 'wholesale_margin_percentage', 'price_slabs', 'use_margin_pricing']);
        });
    }
};
