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
        Schema::table('pos_sales', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_sales', 'invoice_type')) {
                $table->string('invoice_type')->default('Retail')->after('invoice_no');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'invoice_type')) {
                $table->string('invoice_type')->default('Retail')->after('po_number');
            }
            if (! Schema::hasColumn('purchase_orders', 'is_urd')) {
                $table->boolean('is_urd')->default(false)->after('invoice_type');
            }
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('supplier_payments', 'metal_type')) {
                $table->string('metal_type')->nullable()->after('payment_method');
                $table->decimal('metal_weight', 15, 3)->default(0)->after('metal_type');
                $table->decimal('metal_rate', 15, 2)->nullable()->after('metal_weight');
                $table->boolean('is_bhav_cut')->default(false)->after('metal_rate');
            }
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_items', 'is_pata')) {
                $table->boolean('is_pata')->default(false)->after('material_type');
                $table->decimal('pata_weight', 15, 3)->default(0)->after('is_pata');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropColumn(['invoice_type']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['invoice_type', 'is_urd']);
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropColumn(['metal_type', 'metal_weight', 'metal_rate', 'is_bhav_cut']);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['is_pata', 'pata_weight']);
        });
    }
};
