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
        Schema::table('girvi_items', function (Blueprint $table) {
            $table->foreignId('inventory_product_id')->nullable()->after('girvi_id')->constrained('inventory_products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('girvi_items', function (Blueprint $table) {
            $table->dropForeign(['inventory_product_id']);
            $table->dropColumn('inventory_product_id');
        });
    }
};
