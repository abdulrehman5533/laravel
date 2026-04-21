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
        // Drop foreign key constraints first
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->dropForeign(['pos_customer_id']);
        });

        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropForeign(['pos_customer_id']);
        });

        Schema::dropIfExists('pos_customers');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Since we're removing duplicate customer modules, we don't need to reverse this migration
        // The pos_customers table is no longer needed as we're using crm_customers
    }
};
