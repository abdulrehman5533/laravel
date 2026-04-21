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
        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'opening_balance')) {
                $table->decimal('opening_balance', 15, 2)->default(0)->after('tax_id');
            }
            $table->decimal('opening_gold_balance', 15, 3)->default(0)->after('opening_balance');
            $table->decimal('opening_silver_balance', 15, 3)->default(0)->after('opening_gold_balance');
            $table->decimal('current_gold_balance', 15, 3)->default(0)->after('current_balance');
            $table->decimal('current_silver_balance', 15, 3)->default(0)->after('current_gold_balance');
        });

        Schema::table('customer_ledgers', function (Blueprint $table) {
            $table->decimal('opening_gold_balance', 15, 3)->default(0)->after('opening_balance');
            $table->decimal('opening_silver_balance', 15, 3)->default(0)->after('opening_gold_balance');
            $table->decimal('current_gold_balance', 15, 3)->default(0)->after('current_balance');
            $table->decimal('current_silver_balance', 15, 3)->default(0)->after('current_gold_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['opening_gold_balance', 'opening_silver_balance', 'current_gold_balance', 'current_silver_balance']);
        });

        Schema::table('customer_ledgers', function (Blueprint $table) {
            $table->dropColumn(['opening_gold_balance', 'opening_silver_balance', 'current_gold_balance', 'current_silver_balance']);
        });
    }
};
