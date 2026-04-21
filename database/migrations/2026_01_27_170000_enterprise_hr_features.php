<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Shifts & Scheduling
        Schema::create('hr_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('grace_period_minutes')->default(15);
            $table->boolean('is_night_shift')->default(false);
            $table->string('color_code')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_employee_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('hr_shifts')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        // 2. Leave Management
        Schema::create('hr_leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('annual_allocation')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('carry_forward')->default(false);
            $table->integer('max_carry_forward')->default(0);
            $table->timestamps();
        });

        Schema::create('hr_leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('hr_leave_types')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_taken', 5, 1);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        // 3. Performance & Talent
        Schema::create('hr_performance_kpis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('weightage')->default(0); // Percentage
            $table->string('target_type'); // number, percentage, boolean
            $table->timestamps();
        });

        Schema::create('hr_appraisals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->date('appraisal_date');
            $table->json('kpi_scores'); // Stores scores for each KPI
            $table->decimal('total_score', 5, 2);
            $table->text('feedback')->nullable();
            $table->enum('status', ['draft', 'submitted', 'reviewed', 'finalized'])->default('draft');
            $table->timestamps();
        });

        // 4. Documents & Lifecycle
        Schema::create('hr_employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('document_type'); // contract, id, certification, etc.
            $table->string('file_path');
            $table->date('expiry_date')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        Schema::create('hr_lifecycle_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('event_type'); // promotion, transfer, probation_end, resignation, etc.
            $table->date('effective_date');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Previous vs New details
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });

        // 5. Training
        Schema::create('hr_training_programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('trainer_name')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_employee_training', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('training_program_id')->constrained('hr_training_programs')->onDelete('cascade');
            $table->enum('status', ['enrolled', 'in_progress', 'completed', 'failed'])->default('enrolled');
            $table->date('completion_date')->nullable();
            $table->string('certificate_path')->nullable();
            $table->timestamps();
        });

        // 6. Loans & Advances
        Schema::create('hr_employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->integer('repayment_months')->default(1);
            $table->decimal('monthly_installment', 15, 2);
            $table->decimal('remaining_balance', 15, 2);
            $table->date('disbursement_date');
            $table->enum('status', ['pending', 'active', 'paid_off', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // 7. Expenses (Employee Specific)
        Schema::create('hr_employee_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->date('expense_date');
            $table->string('category');
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'reimbursed'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_expenses');
        Schema::dropIfExists('hr_employee_loans');
        Schema::dropIfExists('hr_employee_training');
        Schema::dropIfExists('hr_training_programs');
        Schema::dropIfExists('hr_lifecycle_events');
        Schema::dropIfExists('hr_employee_documents');
        Schema::dropIfExists('hr_appraisals');
        Schema::dropIfExists('hr_performance_kpis');
        Schema::dropIfExists('hr_leave_requests');
        Schema::dropIfExists('hr_leave_types');
        Schema::dropIfExists('hr_employee_shifts');
        Schema::dropIfExists('hr_shifts');
    }
};
