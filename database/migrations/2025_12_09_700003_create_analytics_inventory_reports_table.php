<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_inventory_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->enum('material_type', ['gold', 'silver', 'diamond', 'gems', 'all'])->default('all');
            $table->decimal('total_quantity', 12, 3)->default(0);
            $table->decimal('total_value', 15, 2)->default(0);
            $table->decimal('low_stock_quantity', 12, 3)->default(0);
            $table->integer('low_stock_products')->default(0);
            $table->integer('out_of_stock_products')->default(0);
            $table->decimal('average_stock_value', 15, 2)->default(0);
            $table->integer('total_products')->default(0);
            $table->integer('active_products')->default(0);
            $table->text('low_stock_items')->nullable(); // JSON
            $table->decimal('inventory_turnover_ratio', 8, 2)->nullable();
            $table->timestamps();

            $table->index(['report_date', 'material_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_inventory_reports');
    }
};
