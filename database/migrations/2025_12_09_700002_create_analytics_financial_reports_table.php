<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_financial_reports', function (Blueprint $table) {
            $table->id();
            $table->date('report_date');
            $table->enum('period', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('daily');
            $table->decimal('total_revenue', 15, 2)->default(0);
            $table->decimal('cost_of_goods', 15, 2)->default(0);
            $table->decimal('gross_profit', 15, 2)->default(0);
            $table->decimal('operating_expenses', 15, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);
            $table->decimal('profit_margin_percentage', 5, 2)->default(0);
            $table->decimal('gst_payable', 15, 2)->default(0);
            $table->decimal('gst_receivable', 15, 2)->default(0);
            $table->decimal('total_receivables', 15, 2)->default(0);
            $table->decimal('total_payables', 15, 2)->default(0);
            $table->decimal('cash_in_hand', 15, 2)->default(0);
            $table->decimal('bank_balance', 15, 2)->default(0);
            $table->text('breakdown')->nullable(); // JSON with category-wise breakdown
            $table->timestamps();

            $table->index(['report_date', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_financial_reports');
    }
};
