<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stone_attributes', function (Blueprint $table) {
            $table->string('shape')->nullable()->after('stone_type'); // Round, Pear, Marquise
            $table->string('fluorescence')->nullable()->after('color'); // None, Faint, Medium, Strong
            $table->string('lab')->nullable()->after('certification'); // GIA, IGI, HRD
            $table->decimal('price_per_carat', 15, 2)->nullable()->after('carat');
            $table->decimal('total_stone_value', 15, 2)->nullable()->after('price_per_carat');
        });

        Schema::table('inventory_products', function (Blueprint $table) {
            $table->enum('valuation_method', ['fixed', 'calculated'])->default('fixed')->after('selling_price');
        });
    }

    public function down(): void
    {
        Schema::table('stone_attributes', function (Blueprint $table) {
            $table->dropColumn(['shape', 'fluorescence', 'lab', 'price_per_carat', 'total_stone_value']);
        });

        Schema::table('inventory_products', function (Blueprint $table) {
            $table->dropColumn('valuation_method');
        });
    }
};
