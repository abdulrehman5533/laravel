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
        Schema::table('pos_sale_items', function (Blueprint $table) {
            $table->decimal('making_charge_amount', 16, 2)->default(0)->after('making_charge_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sale_items', function (Blueprint $table) {
            $table->dropColumn('making_charge_amount');
        });
    }
};
