<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('hra', 15, 2)->default(0)->after('base_salary');
            $table->decimal('medical_allowance', 15, 2)->default(0)->after('hra');
            $table->decimal('transport_allowance', 15, 2)->default(0)->after('medical_allowance');
            $table->decimal('eobi_deduction', 15, 2)->default(0)->after('transport_allowance');
            $table->decimal('pessi_deduction', 15, 2)->default(0)->after('eobi_deduction');
            $table->decimal('income_tax', 15, 2)->default(0)->after('pessi_deduction');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['hra', 'medical_allowance', 'transport_allowance', 'eobi_deduction', 'pessi_deduction', 'income_tax']);
        });
    }
};
