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
        Schema::table('supplier_ledger_entries', function (Blueprint $table) {
            $table->decimal('gross_weight', 15, 4)->default(0)->after('amount');
            $table->decimal('fine_weight', 15, 4)->default(0)->after('gross_weight');
            $table->decimal('running_gross_weight', 15, 4)->default(0)->after('running_balance');
            $table->decimal('running_fine_weight', 15, 4)->default(0)->after('running_gross_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_ledger_entries', function (Blueprint $table) {
            $table->dropColumn(['gross_weight', 'fine_weight', 'running_gross_weight', 'running_fine_weight']);
        });
    }
};
