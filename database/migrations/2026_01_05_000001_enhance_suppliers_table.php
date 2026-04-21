<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (! Schema::hasColumn('suppliers', 'company_name')) {
                $table->string('company_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('suppliers', 'ntn')) {
                $table->string('ntn')->unique()->nullable()->after('gstin');
            }
            if (! Schema::hasColumn('suppliers', 'opening_balance')) {
                $table->decimal('opening_balance', 15, 2)->default(0)->after('current_credit_used');
            }
            if (! Schema::hasColumn('suppliers', 'opening_balance_date')) {
                $table->date('opening_balance_date')->nullable()->after('opening_balance');
            }
            if (! Schema::hasColumn('suppliers', 'total_paid_amount')) {
                $table->decimal('total_paid_amount', 15, 2)->default(0)->after('total_purchased_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $columns = ['company_name', 'ntn', 'opening_balance', 'opening_balance_date', 'total_paid_amount'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
