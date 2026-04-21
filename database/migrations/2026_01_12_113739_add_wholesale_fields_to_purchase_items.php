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
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->string('stamp')->nullable()->after('item_code');
            $table->decimal('gross_weight', 15, 4)->default(0)->after('stamp');
            $table->decimal('less_weight', 15, 4)->default(0)->after('gross_weight');
            $table->decimal('net_weight', 15, 4)->default(0)->after('less_weight');
            $table->decimal('tunch', 8, 4)->default(0)->after('net_weight');
            $table->decimal('wastage', 15, 4)->default(0)->after('tunch');
            $table->decimal('fine', 15, 4)->default(0)->after('wastage');
            $table->decimal('labour_charge', 15, 2)->default(0)->after('fine');
            $table->string('tunch_testing_status')->default('not_needed')->after('labour_charge');
            $table->boolean('is_approved')->default(true)->after('tunch_testing_status');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            // Adding wholesale statuses
            // Note: In production Laravel 11, we can use change() for enums if doctrine/dbal is installed or using native MariaDB/MySQL support
            $table->string('status')->default('Draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn([
                'stamp', 'gross_weight', 'less_weight', 'net_weight',
                'tunch', 'wastage', 'fine', 'labour_charge',
                'tunch_testing_status', 'is_approved',
            ]);
        });
    }
};
