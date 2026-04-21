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
            $table->decimal('gross_weight', 12, 4)->nullable()->after('weight');
            $table->decimal('stone_weight', 12, 4)->nullable()->after('gross_weight');
            $table->decimal('net_weight', 12, 4)->nullable()->after('stone_weight');
            $table->enum('making_charge_type', ['fixed', 'per_gram', 'per_piece'])->default('fixed')->after('making_charge');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sale_items', function (Blueprint $table) {
            $table->dropColumn(['gross_weight', 'stone_weight', 'net_weight', 'making_charge_type']);
        });
    }
};
