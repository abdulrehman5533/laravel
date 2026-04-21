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
            $table->decimal('wastage_weight', 10, 4)->nullable()->after('wastage_percent');
            $table->decimal('melting_loss', 10, 4)->nullable()->after('wastage_weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sale_items', function (Blueprint $table) {
            $table->dropColumn(['wastage_weight', 'melting_loss']);
        });
    }
};
