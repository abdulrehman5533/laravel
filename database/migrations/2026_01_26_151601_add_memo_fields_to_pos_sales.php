<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->boolean('is_memo')->default(false)->after('is_wholesale');
            $table->timestamp('memo_expiry_date')->nullable()->after('is_memo');
            $table->enum('consignment_status', ['none', 'issued', 'returned', 'converted_to_sale'])->default('none')->after('memo_expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('pos_sales', function (Blueprint $table) {
            $table->dropColumn(['is_memo', 'memo_expiry_date', 'consignment_status']);
        });
    }
};
