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
            $table->decimal('metal_payment_gold', 15, 3)->default(0)->after('payment_method');
            $table->decimal('metal_payment_silver', 15, 3)->default(0)->after('metal_payment_gold');
            $table->decimal('metal_rate_gold', 15, 2)->nullable()->after('metal_payment_silver');
            $table->decimal('metal_rate_silver', 15, 2)->nullable()->after('metal_rate_gold');

            $table->decimal('previous_balance_cash', 15, 2)->default(0)->after('total');
            $table->decimal('previous_balance_gold', 15, 3)->default(0)->after('previous_balance_cash');
            $table->decimal('previous_balance_silver', 15, 3)->default(0)->after('previous_balance_gold');

            $table->timestamp('emailed_at')->nullable()->after('stock_moved');
            $table->timestamp('printed_at')->nullable()->after('emailed_at');
            $table->string('email_status')->nullable()->after('printed_at');
        });

        Schema::table('pos_invoices', function (Blueprint $table) {
            $table->timestamp('emailed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropColumn([
                'metal_payment_gold', 'metal_payment_silver', 'metal_rate_gold', 'metal_rate_silver',
                'previous_balance_cash', 'previous_balance_gold', 'previous_balance_silver',
                'emailed_at', 'printed_at', 'email_status',
            ]);
        });

        Schema::table('pos_invoices', function (Blueprint $table) {
            $table->dropColumn('emailed_at');
        });
    }
};
