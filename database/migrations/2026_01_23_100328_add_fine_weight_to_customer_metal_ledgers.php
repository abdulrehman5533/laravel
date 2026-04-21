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
        Schema::table('customer_metal_ledgers', function (Blueprint $table) {
            $table->decimal('fine_weight_in', 15, 3)->default(0)->after('weight_in');
            $table->decimal('fine_weight_out', 15, 3)->default(0)->after('weight_out');
            $table->decimal('running_fine_balance', 15, 3)->default(0)->after('running_weight_balance');
            $table->decimal('purity_percentage', 8, 4)->nullable()->after('metal_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_metal_ledgers', function (Blueprint $table) {
            $table->dropColumn(['fine_weight_in', 'fine_weight_out', 'running_fine_balance', 'purity_percentage']);
        });
    }
};
