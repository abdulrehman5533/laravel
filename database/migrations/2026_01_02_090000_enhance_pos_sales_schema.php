<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->string('discount_type')->default('fixed')->after('discount');
            $table->decimal('discount_value', 16, 2)->default(0)->after('discount_type');
            $table->decimal('outstanding_balance', 16, 2)->default(0)->after('total');
            $table->integer('loyalty_points_earned')->default(0)->after('outstanding_balance');
            $table->integer('loyalty_points_used')->default(0)->after('loyalty_points_earned');
            $table->string('payment_status')->default('unpaid')->after('status');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->json('audit_log')->nullable()->after('meta');
            $table->json('installment_plan')->nullable()->after('audit_log');

            if (Schema::hasColumn('pos_sales', 'due_date') === false) {
                $table->date('due_date')->nullable()->after('sale_time');
            }
        });

        Schema::table('pos_sale_items', function (Blueprint $table) {
            if (Schema::hasColumn('pos_sale_items', 'weight') === false) {
                $table->decimal('weight', 16, 4)->nullable()->after('unit_price');
            }
            if (Schema::hasColumn('pos_sale_items', 'gold_rate') === false) {
                $table->decimal('gold_rate', 16, 2)->nullable()->after('weight');
            }
            if (Schema::hasColumn('pos_sale_items', 'gold_purity') === false) {
                $table->string('gold_purity')->nullable()->after('gold_rate');
            }
            if (Schema::hasColumn('pos_sale_items', 'stone_count') === false) {
                $table->integer('stone_count')->default(0)->after('gold_purity');
            }
            if (Schema::hasColumn('pos_sale_items', 'stone_carat') === false) {
                $table->decimal('stone_carat', 16, 4)->nullable()->after('stone_count');
            }
            if (Schema::hasColumn('pos_sale_items', 'stone_type') === false) {
                $table->string('stone_type')->nullable()->after('stone_carat');
            }
            if (Schema::hasColumn('pos_sale_items', 'stone_price') === false) {
                $table->decimal('stone_price', 16, 2)->default(0)->after('stone_type');
            }
            if (Schema::hasColumn('pos_sale_items', 'discount_percent') === false) {
                $table->decimal('discount_percent', 8, 3)->default(0)->after('stone_price');
            }
            if (Schema::hasColumn('pos_sale_items', 'discount_amount') === false) {
                $table->decimal('discount_amount', 16, 2)->default(0)->after('discount_percent');
            }
        });

        Schema::table('pos_payments', function (Blueprint $table) {
            if (Schema::hasColumn('pos_payments', 'bank_name') === false) {
                $table->string('bank_name')->nullable()->after('payment_method');
            }
            if (Schema::hasColumn('pos_payments', 'cheque_number') === false) {
                $table->string('cheque_number')->nullable()->after('bank_name');
            }
            if (Schema::hasColumn('pos_payments', 'transaction_id') === false) {
                $table->string('transaction_id')->nullable()->after('cheque_number');
            }
            if (Schema::hasColumn('pos_payments', 'notes') === false) {
                $table->text('notes')->nullable()->after('reference');
            }
            if (Schema::hasColumn('pos_payments', 'recorded_by') === false) {
                $table->unsignedBigInteger('recorded_by')->nullable()->after('reference');
            }
        });
    }

    public function down()
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $columns = ['discount_type', 'discount_value', 'outstanding_balance',
                'loyalty_points_earned', 'loyalty_points_used', 'payment_status',
                'updated_by', 'audit_log', 'installment_plan', 'due_date'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('pos_sales', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('pos_sale_items', function (Blueprint $table) {
            $columns = ['weight', 'gold_rate', 'gold_purity', 'stone_count',
                'stone_carat', 'stone_type', 'stone_price', 'discount_percent', 'discount_amount'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('pos_sale_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('pos_payments', function (Blueprint $table) {
            $columns = ['bank_name', 'cheque_number', 'transaction_id', 'notes', 'recorded_by'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('pos_payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
