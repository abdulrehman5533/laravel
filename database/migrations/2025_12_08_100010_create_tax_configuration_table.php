<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_configurations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->string('tax_type'); // GST, VAT, Income Tax
            $table->decimal('rate', 5, 2);
            $table->string('applicable_to'); // sales, purchases, both
            $table->string('product_category')->nullable(); // gold, gemstone, labour, etc.
            $table->string('hsn_sac_code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->timestamps();

            $table->unique(['tax_type', 'rate', 'product_category', 'branch_id'], 'tax_config_unique');
        });

        Schema::create('tax_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_config_id')->constrained('tax_configurations')->onDelete('cascade');
            $table->date('date');
            $table->string('transaction_type'); // sale, purchase
            $table->string('reference_type'); // sale, purchase, expense
            $table->unsignedBigInteger('reference_id');
            $table->decimal('taxable_amount', 12, 2);
            $table->decimal('tax_amount', 12, 2);
            $table->string('status')->default('recorded'); // recorded, reported, paid
            $table->timestamps();

            $table->index(['date']);
            $table->index(['transaction_type', 'date']);
        });

        Schema::create('tax_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('tax_type');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_taxable_sales', 12, 2)->default(0);
            $table->decimal('total_tax_on_sales', 12, 2)->default(0);
            $table->decimal('total_taxable_purchases', 12, 2)->default(0);
            $table->decimal('total_tax_on_purchases', 12, 2)->default(0);
            $table->decimal('net_tax_payable', 12, 2)->default(0);
            $table->string('status')->default('draft'); // draft, submitted, approved
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_reports');
        Schema::dropIfExists('tax_entries');
        Schema::dropIfExists('tax_configurations');
    }
};
