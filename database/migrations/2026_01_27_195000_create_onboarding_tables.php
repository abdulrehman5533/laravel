<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_onboarding_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('department')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hr_onboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('hr_onboarding_checklists')->onDelete('cascade');
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('hr_employee_onboarding', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('checklist_id')->constrained('hr_onboarding_checklists')->onDelete('cascade');
            $table->enum('status', ['in_progress', 'completed', 'cancelled'])->default('in_progress');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_employee_onboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarding_id')->constrained('hr_employee_onboarding')->onDelete('cascade');
            $table->foreignId('task_id')->constrained('hr_onboarding_tasks')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_onboarding_tasks');
        Schema::dropIfExists('hr_employee_onboarding');
        Schema::dropIfExists('hr_onboarding_tasks');
        Schema::dropIfExists('hr_onboarding_checklists');
    }
};
