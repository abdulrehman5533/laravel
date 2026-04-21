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
        Schema::table('girvis', function (Blueprint $table) {
            if (! Schema::hasColumn('girvis', 'grace_period_days')) {
                $table->integer('grace_period_days')->default(0)->after('interest_cycle');
            }
            if (! Schema::hasColumn('girvis', 'penal_interest_rate')) {
                $table->decimal('penal_interest_rate', 5, 2)->default(0)->after('interest_rate');
            }
            if (! Schema::hasColumn('girvis', 'rounding_rule')) {
                $table->string('rounding_rule')->default('none')->after('interest_type');
            }
            if (! Schema::hasColumn('girvis', 'rate_source')) {
                $table->string('rate_source')->nullable()->after('locked_silver_rate');
            }
            if (! Schema::hasColumn('girvis', 'risk_alert_threshold')) {
                $table->decimal('risk_alert_threshold', 5, 2)->nullable()->after('ltv_ratio');
            }
            if (! Schema::hasColumn('girvis', 'is_high_risk')) {
                $table->boolean('is_high_risk')->default(false)->after('risk_score');
            }
            if (! Schema::hasColumn('girvis', 'transfer_charges')) {
                $table->decimal('transfer_charges', 15, 2)->default(0)->after('transfer_amount');
            }
            if (! Schema::hasColumn('girvis', 'transfer_terms')) {
                $table->text('transfer_terms')->nullable()->after('transfer_charges');
            }
        });

        Schema::table('girvi_items', function (Blueprint $table) {
            if (! Schema::hasColumn('girvi_items', 'stone_weight')) {
                $table->decimal('stone_weight', 10, 3)->default(0)->after('net_weight');
            }
            if (! Schema::hasColumn('girvi_items', 'fine_weight')) {
                $table->decimal('fine_weight', 10, 3)->default(0)->after('stone_weight');
            }
            if (! Schema::hasColumn('girvi_items', 'item_condition')) {
                $table->string('item_condition')->nullable()->after('fine_weight');
            }
            if (! Schema::hasColumn('girvi_items', 'barcode')) {
                $table->string('barcode')->nullable()->unique()->after('item_photo');
            }
            if (! Schema::hasColumn('girvi_items', 'qr_code')) {
                $table->string('qr_code')->nullable()->unique()->after('barcode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('girvis', function (Blueprint $table) {
            $table->dropColumn([
                'grace_period_days', 'penal_interest_rate', 'rounding_rule',
                'rate_source', 'risk_alert_threshold', 'is_high_risk',
                'transfer_charges', 'transfer_terms',
            ]);
        });

        Schema::table('girvi_items', function (Blueprint $table) {
            $table->dropColumn([
                'stone_weight', 'fine_weight', 'item_condition', 'barcode', 'qr_code',
            ]);
        });
    }
};
