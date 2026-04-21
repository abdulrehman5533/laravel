<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add missing fields to inventory_products from jewellery_products
        Schema::table('inventory_products', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_products', 'hallmark')) {
                $table->string('hallmark')->nullable()->after('barcode');
            }
            if (! Schema::hasColumn('inventory_products', 'certificate_no')) {
                $table->string('certificate_no')->nullable()->after('hallmark');
            }
            if (! Schema::hasColumn('inventory_products', 'making_charge_type')) {
                $table->string('making_charge_type')->default('fixed')->after('selling_price'); // fixed, per_gram
            }
            if (! Schema::hasColumn('inventory_products', 'making_charge_value')) {
                $table->decimal('making_charge_value', 16, 2)->default(0)->after('making_charge_type');
            }
        });

        // 2. Ensure pos_sales has all fields from sales
        Schema::table('pos_sales', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_sales', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_status'); // For quick reference
            }
            if (! Schema::hasColumn('pos_sales', 'notes')) {
                $table->text('notes')->nullable()->after('payment_method');
            }
        });

        // 3. Ensure pos_sale_items points to inventory_products (already true but confirming)
    }

    public function down(): void
    {
        Schema::table('inventory_products', function (Blueprint $table) {
            $table->dropColumn(['hallmark', 'certificate_no', 'making_charge_type', 'making_charge_value']);
        });

        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'notes']);
        });
    }
};
