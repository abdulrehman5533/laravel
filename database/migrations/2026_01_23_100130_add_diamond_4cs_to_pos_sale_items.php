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
            $table->string('stone_cut')->nullable()->after('stone_type');
            $table->string('stone_clarity')->nullable()->after('stone_cut');
            $table->string('stone_color')->nullable()->after('stone_clarity');
            $table->string('stone_certification')->nullable()->after('stone_color');
            $table->string('certificate_no')->nullable()->after('stone_certification');
            $table->decimal('stone_price_per_carat', 15, 2)->nullable()->after('stone_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sale_items', function (Blueprint $table) {
            $table->dropColumn([
                'stone_cut', 'stone_clarity', 'stone_color',
                'stone_certification', 'certificate_no', 'stone_price_per_carat',
            ]);
        });
    }
};
