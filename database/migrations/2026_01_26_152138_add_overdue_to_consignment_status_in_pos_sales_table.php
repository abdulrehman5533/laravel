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
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->enum('consignment_status', ['none', 'issued', 'returned', 'converted_to_sale', 'overdue'])
                ->default('none')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->enum('consignment_status', ['none', 'issued', 'returned', 'converted_to_sale'])
                ->default('none')
                ->change();
        });
    }
};
