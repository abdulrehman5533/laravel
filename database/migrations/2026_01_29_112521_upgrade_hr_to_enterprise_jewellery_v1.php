<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Salary Components (Earnings/Deductions)
        Schema::create('hr_salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., HRA, Fuel Allowance, Provident Fund
            $table->enum('type', ['earning', 'deduction']);
            $table->enum('calculation_type', ['fixed', 'percentage_of_basic']);
            $table->decimal('default_value', 15, 2)->default(0);
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_mandatory')->default(false);
            $table->timestamps();
        });

        // 2. Employee Specific Salary Structure
        Schema::create('hr_employee_salary_structures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('component_id')->constrained('hr_salary_components')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });

        // 3. Karigar Making Charge Rates
        Schema::create('hr_karigar_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // Karigar
            $table->string('item_category'); // Ring, Necklace, etc.
            $table->decimal('rate_per_gram', 10, 2)->default(0);
            $table->decimal('rate_per_piece', 10, 2)->default(0);
            $table->decimal('wastage_limit_percent', 5, 2)->default(0);
            $table->timestamps();
        });

        // 4. Sales Commission Rates
        Schema::create('hr_sales_commission_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade'); // Sales Staff
            $table->string('product_category'); // Gold, Diamond, Silver
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->decimal('target_amount', 15, 2)->nullable();
            $table->timestamps();
        });

        // 5. Attendance Rules (Enterprise Level)
        Schema::create('hr_attendance_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name');
            $table->time('shift_start');
            $table->time('shift_end');
            $table->integer('grace_time_minutes')->default(15);
            $table->integer('half_day_late_minutes')->default(120);
            $table->decimal('ot_rate_multiplier', 4, 2)->default(1.5); // 1.5x for OT
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Update Payroll table to include new enterprise fields
        Schema::table('hr_payroll', function (Blueprint $table) {
            $table->decimal('karigar_making_charges', 15, 2)->default(0)->after('bonus_performance');
            $table->decimal('sales_commission', 15, 2)->default(0)->after('karigar_making_charges');
            $table->decimal('late_deduction', 15, 2)->default(0)->after('tax_amount');
            $table->decimal('overtime_pay', 15, 2)->default(0)->after('sales_commission');
            $table->json('salary_breakdown')->nullable()->after('compliance_details'); // Breakdown of HRA, PF, etc.
        });
    }

    public function down(): void
    {
        Schema::table('hr_payroll', function (Blueprint $table) {
            $table->dropColumn(['karigar_making_charges', 'sales_commission', 'late_deduction', 'overtime_pay', 'salary_breakdown']);
        });
        Schema::dropIfExists('hr_attendance_rules');
        Schema::dropIfExists('hr_sales_commission_rates');
        Schema::dropIfExists('hr_karigar_rates');
        Schema::dropIfExists('hr_employee_salary_structures');
        Schema::dropIfExists('hr_salary_components');
    }
};
