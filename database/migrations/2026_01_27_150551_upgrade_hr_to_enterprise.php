<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('country_code', 5)->default('IN');
            $table->string('biometric_id')->nullable();
            $table->decimal('performance_score', 5, 2)->default(0);
            $table->json('skills')->nullable();
            $table->string('onboarding_status')->default('pending'); // pending, completed
        });

        Schema::table('hr_payroll', function (Blueprint $table) {
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('social_security_contribution', 15, 2)->default(0);
            $table->decimal('bonus_performance', 15, 2)->default(0);
            $table->json('compliance_details')->nullable(); // Multi-country compliance logs
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'biometric_id', 'performance_score', 'skills', 'onboarding_status']);
        });

        Schema::table('hr_payroll', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'social_security_contribution', 'bonus_performance', 'compliance_details']);
        });
    }
};
