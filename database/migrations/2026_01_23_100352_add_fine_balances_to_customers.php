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
            $table->decimal('current_fine_gold_balance', 15, 3)->default(0)->after('current_gold_balance');
            $table->decimal('current_fine_silver_balance', 15, 3)->default(0)->after('current_silver_balance');
            $table->decimal('opening_fine_gold_balance', 15, 3)->default(0)->after('opening_gold_balance');
            $table->decimal('opening_fine_silver_balance', 15, 3)->default(0)->after('opening_silver_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'current_fine_gold_balance', 'current_fine_silver_balance',
                'opening_fine_gold_balance', 'opening_fine_silver_balance',
            ]);
        });
    }
};
