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
        Schema::table('gold_savings_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('gold_savings_scheme_id')->after('id');
            $table->decimal('amount', 12, 2)->after('gold_savings_scheme_id');
            $table->decimal('gold_rate', 10, 2)->after('amount');
            $table->decimal('gold_weight', 10, 4)->after('gold_rate');
            $table->date('payment_date')->after('gold_weight');
            $table->string('notes')->nullable()->after('payment_date');
        });
    }

    public function down(): void
    {
        Schema::table('gold_savings_payments', function (Blueprint $table) {
            $table->dropColumn(['gold_savings_scheme_id','amount','gold_rate','gold_weight','payment_date','notes']);
        });
    }
};
