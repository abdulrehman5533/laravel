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
            $table->foreign('pos_customer_id')->references('id')->on('customers')->onDelete('set null');
        });

        Schema::table('pos_payments', function (Blueprint $table) {
            $table->foreign('pos_customer_id')->references('id')->on('customers')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropForeign(['pos_customer_id']);
        });

        Schema::table('pos_payments', function (Blueprint $table) {
            $table->dropForeign(['pos_customer_id']);
        });
    }
};
