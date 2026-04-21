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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('employees', 'probation_end_date')) {
                $table->date('probation_end_date')->nullable();
            }
            if (!Schema::hasColumn('employees', 'base_salary')) {
                $table->decimal('base_salary', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('employees', 'commission_percentage')) {
                $table->decimal('commission_percentage', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('employees', 'bank_details')) {
                $table->json('bank_details')->nullable();
            }
            if (!Schema::hasColumn('employees', 'status')) {
                $table->string('status')->default('active');
            }
            if (!Schema::hasColumn('employees', 'emergency_contact')) {
                $table->json('emergency_contact')->nullable();
            }
            if (!Schema::hasColumn('employees', 'tenant_id')) {
                $table->unsignedBigInteger('tenant_id')->nullable();
            }
            if (!Schema::hasColumn('employees', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (!Schema::hasColumn('employees', 'gender')) {
                $table->string('gender')->nullable();
            }
            if (!Schema::hasColumn('employees', 'national_id')) {
                $table->string('national_id')->nullable();
            }
            if (!Schema::hasColumn('employees', 'tax_id')) {
                $table->string('tax_id')->nullable();
            }
            if (!Schema::hasColumn('employees', 'biometric_id')) {
                $table->string('biometric_id')->nullable();
            }
            if (!Schema::hasColumn('employees', 'country_code')) {
                $table->string('country_code', 5)->nullable();
            }
            if (!Schema::hasColumn('employees', 'skills')) {
                $table->json('skills')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'address', 'probation_end_date', 'base_salary', 'commission_percentage', 
                'bank_details', 'status', 'emergency_contact', 'tenant_id',
                'date_of_birth', 'gender', 'national_id', 'tax_id', 'biometric_id', 'country_code', 'skills'
            ]);
        });
    }
};
