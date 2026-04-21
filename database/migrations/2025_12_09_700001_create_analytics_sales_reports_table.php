<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_sales_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->enum('period', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('daily');
            $table->enum('material_type', ['gold', 'silver', 'diamond', 'gems', 'all'])->default('all');
            $table->decimal('total_sales', 15, 2)->default(0);
            $table->integer('transaction_count')->default(0);
            $table->decimal('average_transaction', 15, 2)->default(0);
            $table->decimal('total_quantity', 12, 3)->default(0);
            $table->decimal('gst_collected', 15, 2)->default(0);
            $table->decimal('discount_given', 15, 2)->default(0);
            $table->decimal('net_sales', 15, 2)->default(0);
            $table->integer('unique_customers')->default(0);
            $table->integer('repeat_customers')->default(0);
            $table->integer('vip_transactions')->default(0);
            $table->decimal('vip_sales', 15, 2)->default(0);
            $table->integer('wholesale_transactions')->default(0);
            $table->decimal('wholesale_sales', 15, 2)->default(0);
            $table->text('top_products')->nullable(); // JSON
            $table->text('top_customers')->nullable(); // JSON
            $table->timestamps();

            $table->index(['report_date', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_sales_reports');
    }
};
