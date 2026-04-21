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
        Schema::table('girvi_items', function (Blueprint $table) {
            $table->unsignedBigInteger('vault_id')->nullable()->after('girvi_id');
            $table->string('box_number')->nullable()->after('vault_id');
            $table->string('status')->default('in_vault')->after('box_number'); // in_vault, out_for_appraisal, auctioned, redeemed

            $table->foreign('vault_id')->references('id')->on('vaults')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('girvi_items', function (Blueprint $table) {
            $table->dropForeign(['vault_id']);
            $table->dropColumn(['vault_id', 'box_number', 'status']);
        });
    }
};
