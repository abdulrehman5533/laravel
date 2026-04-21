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
        Schema::table('service_job_items', function (Blueprint $table) {
            $table->decimal('purity_issued', 5, 2)->default(100)->after('purity_expected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_job_items', function (Blueprint $table) {
            $table->dropColumn('purity_issued');
        });
    }
};
