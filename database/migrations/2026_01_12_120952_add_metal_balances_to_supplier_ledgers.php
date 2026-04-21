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
        Schema::table('supplier_ledgers', function (Blueprint $table) {
            $table->decimal('opening_gross_weight', 15, 4)->default(0)->after('opening_balance');
            $table->decimal('opening_fine_weight', 15, 4)->default(0)->after('opening_gross_weight');
            $table->decimal('current_gross_weight', 15, 4)->default(0)->after('current_balance');
            $table->decimal('current_fine_weight', 15, 4)->default(0)->after('current_gross_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_ledgers', function (Blueprint $table) {
            $table->dropColumn(['opening_gross_weight', 'opening_fine_weight', 'current_gross_weight', 'current_fine_weight']);
        });
    }
};
