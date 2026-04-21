<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedBigInteger('gold_rate_id')->nullable()->after('customer_id');
            $table->timestamp('rate_locked_at')->nullable()->after('gold_rate_id');

            $table->foreign('gold_rate_id')->references('id')->on('gold_rates');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['gold_rate_id']);
            $table->dropColumn(['gold_rate_id', 'rate_locked_at']);
        });
    }
};
