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
            $table->string('payment_terms', 100)->change();
            $table->string('supplier_type', 100)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->enum('payment_terms', ['COD', 'NET_15', 'NET_30', 'NET_45', 'NET_60', 'CUSTOM'])->default('NET_30')->change();
            $table->enum('supplier_type', ['Precious_Metals', 'Gemstones', 'Diamonds', 'Accessories', 'Mixed'])->default('Mixed')->change();
        });
    }
};
