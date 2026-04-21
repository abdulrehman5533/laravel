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
            $table->string('metal_color')->nullable()->after('purity_id');
            $table->decimal('wastage_percentage', 5, 2)->default(0)->after('weight');
            $table->string('gender')->nullable()->after('name');
            $table->string('collection')->nullable()->after('category_id');
            $table->decimal('length', 8, 2)->nullable()->after('stone_type');
            $table->decimal('width', 8, 2)->nullable()->after('length');
            $table->decimal('height', 8, 2)->nullable()->after('width');
            $table->string('size')->nullable()->after('height');
            $table->boolean('is_hallmarked')->default(false)->after('hallmark');
            $table->decimal('labor_charge', 15, 2)->default(0)->after('making_charge_value');
            $table->string('tag_id')->nullable()->unique()->after('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_products', function (Blueprint $table) {
            $table->dropColumn([
                'metal_color', 'wastage_percentage', 'gender', 'collection',
                'length', 'width', 'height', 'size', 'is_hallmarked',
                'labor_charge', 'tag_id',
            ]);
        });
    }
};
