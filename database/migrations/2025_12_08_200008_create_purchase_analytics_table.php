<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('cascade');

            // Period Information
            $table->year('analysis_year');
            $table->integer('analysis_month');
            $table->date('period_start');
            $table->date('period_end');

            // Purchase Metrics
            $table->integer('total_orders')->default(0);
            $table->decimal('total_purchase_amount', 15, 2)->default(0);
            $table->decimal('average_order_value', 15, 2)->default(0);
            $table->decimal('total_quantity_ordered', 12, 4)->default(0);

            // Fulfillment Metrics
            $table->decimal('on_time_delivery_percentage', 5, 2)->default(0);
            $table->integer('on_time_orders')->default(0);
            $table->integer('late_orders')->default(0);
            $table->decimal('average_delivery_days', 5, 2)->default(0);

            // Quality Metrics
            $table->integer('total_orders_received')->default(0);
            $table->decimal('quality_acceptance_rate', 5, 2)->default(0);
            $table->decimal('defect_rate', 5, 2)->default(0);
            $table->integer('rejected_items')->default(0);
            $table->integer('return_count')->default(0);
            $table->decimal('total_return_amount', 15, 2)->default(0);

            // Payment Metrics
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('average_payment_days', 5, 2)->default(0);
            $table->decimal('payment_on_time_percentage', 5, 2)->default(0);
            $table->decimal('overdue_amount', 15, 2)->default(0);
            $table->integer('overdue_invoices')->default(0);

            // Cost Analysis
            $table->decimal('lowest_price_paid', 15, 4)->default(0);
            $table->decimal('highest_price_paid', 15, 4)->default(0);
            $table->decimal('average_price', 15, 4)->default(0);
            $table->decimal('gst_total', 15, 2)->default(0);

            // Performance Score
            $table->decimal('supplier_performance_score', 5, 2)->default(0);
            $table->decimal('reliability_score', 5, 2)->default(0);
            $table->decimal('quality_score', 5, 2)->default(0);
            $table->decimal('cost_effectiveness_score', 5, 2)->default(0);

            // Trend Analysis
            $table->decimal('month_on_month_growth', 5, 2)->default(0);
            $table->decimal('year_on_year_growth', 5, 2)->default(0);

            // Unique identifier
            $table->unique(['supplier_id', 'branch_id', 'analysis_year', 'analysis_month'], 'purchase_analytics_unique');

            // Audit
            $table->timestamps();

            // Indexes
            $table->index('branch_id');
            $table->index('supplier_id');
            $table->index('analysis_year');
            $table->index('analysis_month');
            $table->index('period_start');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_analytics');
    }
};
