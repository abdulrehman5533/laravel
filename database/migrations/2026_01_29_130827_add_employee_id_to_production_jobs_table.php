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
        Schema::table('production_jobs', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_id')->nullable()->after('karigar_id');
            $table->enum('karigar_type', ['internal', 'external'])->default('external')->after('employee_id');
            $table->decimal('labor_rate_per_gram', 12, 4)->default(0)->after('labor_charges');
            $table->decimal('labor_rate_per_piece', 12, 4)->default(0)->after('labor_rate_per_gram');
            
            $table->foreign('employee_id')->references('id')->on('employees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_jobs', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropColumn(['employee_id', 'karigar_type', 'labor_rate_per_gram', 'labor_rate_per_piece']);
        });
    }
};
