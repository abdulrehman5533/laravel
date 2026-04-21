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
            $table->string('supplier_type', 100)->default('Mixed')->change();
            $table->string('payment_terms', 100)->default('NET_30')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('supplier_type', 100)->default(null)->change();
            $table->string('payment_terms', 100)->default(null)->change();
        });
    }
};
