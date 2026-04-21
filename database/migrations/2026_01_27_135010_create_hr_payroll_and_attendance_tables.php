<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->date('date');
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->string('status')->default('present'); // present, absent, late, half_day
            $table->string('location_coords')->nullable(); // GPS for mobility
            $table->timestamps();
        });

        Schema::create('hr_payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained();
            $table->string('month_year'); // e.g., 01-2026
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('allowances', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('commission', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->string('payment_status')->default('pending'); // pending, approved, paid
            $table->date('paid_on')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_payroll');
        Schema::dropIfExists('hr_attendance');
    }
};
