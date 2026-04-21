<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Upgrade Attendance
        Schema::table('hr_attendance', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_attendance', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('employee_id')->constrained();
            }
            if (!Schema::hasColumn('hr_attendance', 'shift_id')) {
                $table->foreignId('shift_id')->nullable()->after('branch_id')->constrained('hr_shifts');
            }
            $table->string('check_in_ip')->nullable()->after('ip_address');
            $table->string('check_out_ip')->nullable()->after('check_in_ip');
        });

        // 2. Attendance Breaks
        Schema::create('hr_attendance_breaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('hr_attendance')->onDelete('cascade');
            $table->string('break_type')->default('lunch'); // lunch, tea, personal
            $table->timestamp('break_in');
            $table->timestamp('break_out')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Holidays
        Schema::create('hr_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('name');
            $table->date('date');
            $table->boolean('is_optional')->default(false);
            $table->boolean('is_market_holiday')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 4. Upgrade Leave Management
        Schema::table('hr_leave_requests', function (Blueprint $table) {
            $table->string('document_path')->nullable()->after('reason');
            $table->boolean('is_half_day')->default(false)->after('days_taken');
            $table->string('half_day_type')->nullable()->after('is_half_day'); // first_half, second_half
        });

        Schema::create('hr_employee_leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('hr_leave_types')->onDelete('cascade');
            $table->integer('year');
            $table->decimal('allocated_days', 5, 1);
            $table->decimal('used_days', 5, 1)->default(0);
            $table->decimal('remaining_days', 5, 1);
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year'], 'emp_leave_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_leave_balances');
        
        Schema::table('hr_leave_requests', function (Blueprint $table) {
            $table->dropColumn(['document_path', 'is_half_day', 'half_day_type']);
        });

        Schema::dropIfExists('hr_holidays');
        Schema::dropIfExists('hr_attendance_breaks');

        Schema::table('hr_attendance', function (Blueprint $table) {
            $table->dropColumn(['branch_id', 'shift_id', 'check_in_ip', 'check_out_ip']);
        });
    }
};
