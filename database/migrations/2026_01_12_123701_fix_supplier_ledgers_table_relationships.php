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
        // Check if vendor_id exists and supplier_id doesn't
        if (Schema::hasColumn('supplier_ledgers', 'vendor_id') && ! Schema::hasColumn('supplier_ledgers', 'supplier_id')) {
            Schema::table('supplier_ledgers', function (Blueprint $table) {
                // Drop foreign key if it exists
                try {
                    $table->dropForeign(['vendor_id']);
                } catch (\Exception $e) {
                    // Ignore if no foreign key
                }

                $table->renameColumn('vendor_id', 'supplier_id');
            });

            Schema::table('supplier_ledgers', function (Blueprint $table) {
                $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_ledgers', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->renameColumn('supplier_id', 'vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }
};
