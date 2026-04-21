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
        Schema::table('inventory_products', function (Blueprint $table) {
            $table->string('batch_no')->nullable()->after('tag_id');
            $table->string('stamp')->nullable()->after('batch_no');
            $table->decimal('gross_weight', 15, 4)->default(0)->after('weight');
            $table->decimal('net_weight', 15, 4)->default(0)->after('gross_weight');
            $table->decimal('fine_weight', 15, 4)->default(0)->after('net_weight');
            $table->integer('current_pieces')->default(0)->after('current_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_products', function (Blueprint $table) {
            $table->dropColumn(['batch_no', 'stamp', 'gross_weight', 'net_weight', 'fine_weight', 'current_pieces']);
        });
    }
};
