<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Assets Management
        Schema::create('hr_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('asset_tag')->unique();
            $table->string('category'); // Laptop, Mobile, Vehicle, etc.
            $table->string('serial_number')->nullable();
            $table->decimal('value', 15, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->enum('status', ['available', 'assigned', 'under_repair', 'retired'])->default('available');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('hr_assets')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('assigned_at');
            $table->date('returned_at')->nullable();
            $table->text('condition_on_assign')->nullable();
            $table->text('condition_on_return')->nullable();
            $table->foreignId('assigned_by')->constrained('users');
            $table->timestamps();
        });

        // Disciplinary Management
        Schema::create('hr_disciplinary_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('action_type'); // Verbal Warning, Written Warning, Suspension, Termination
            $table->date('incident_date');
            $table->text('reason');
            $table->text('action_taken');
            $table->enum('status', ['pending', 'appealed', 'finalized'])->default('pending');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
        });

        // Interview Feedback (Enhancing Recruitment)
        Schema::create('hr_interview_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->constrained('hr_interviews')->onDelete('cascade');
            $table->foreignId('interviewer_id')->constrained('users')->onDelete('cascade');
            $table->integer('rating'); // 1-5
            $table->text('comments');
            $table->enum('recommendation', ['hire', 'hold', 'reject']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_interview_feedback');
        Schema::dropIfExists('hr_disciplinary_actions');
        Schema::dropIfExists('hr_asset_assignments');
        Schema::dropIfExists('hr_assets');
    }
};
