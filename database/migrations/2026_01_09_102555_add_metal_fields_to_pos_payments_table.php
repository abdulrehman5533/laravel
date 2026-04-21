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
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->string('metal_type')->nullable()->after('amount'); // gold, silver
            $table->decimal('metal_weight', 15, 3)->default(0)->after('metal_type');
            $table->decimal('metal_rate', 15, 2)->nullable()->after('metal_weight');
            $table->boolean('is_bhav_cut')->default(false)->after('metal_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            $table->dropColumn(['metal_type', 'metal_weight', 'metal_rate', 'is_bhav_cut']);
        });
    }
};
