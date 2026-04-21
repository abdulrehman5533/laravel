<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->foreignId('branch_id')->nullable()->constrained(); // Link to branch if specific
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('warehouse_bins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->string('bin_code');
            $table->string('bin_type')->default('storage'); // storage, picking, display
            $table->timestamps();
        });

        Schema::table('inventory_products', function (Blueprint $table) {
            if (! Schema::hasColumn('inventory_products', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->constrained();
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_products', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
        Schema::dropIfExists('warehouse_bins');
        Schema::dropIfExists('warehouses');
    }
};
