<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->integer('maintenance_interval_days')->default(180);
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->string('depreciation_method')->default('straight_line'); // straight_line, reducing_balance
            $table->string('lifecycle_status')->default('operational'); // operational, under_maintenance, retired
            $table->json('maintenance_history')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['maintenance_interval_days', 'last_maintenance_date', 'next_maintenance_date', 'depreciation_method', 'lifecycle_status', 'maintenance_history']);
        });
    }
};
