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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('current_gold_balance', 15, 3)->default(0);
            $table->decimal('current_fine_gold_balance', 15, 3)->default(0);
            $table->decimal('current_silver_balance', 15, 3)->default(0);
            $table->decimal('current_fine_silver_balance', 15, 3)->default(0);
            $table->decimal('opening_gold_balance', 15, 3)->default(0);
            $table->decimal('opening_fine_gold_balance', 15, 3)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'current_gold_balance', 'current_fine_gold_balance',
                'current_silver_balance', 'current_fine_silver_balance',
                'opening_gold_balance', 'opening_fine_gold_balance',
            ]);
        });
    }
};
