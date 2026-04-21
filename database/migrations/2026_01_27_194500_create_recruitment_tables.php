<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('department');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['draft', 'open', 'closed', 'on_hold'])->default('draft');
            $table->date('closing_date')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained('hr_job_postings')->onDelete('cascade');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('resume_path')->nullable();
            $table->enum('status', ['applied', 'screening', 'interviewing', 'offered', 'hired', 'rejected'])->default('applied');
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('hr_candidates')->onDelete('cascade');
            $table->foreignId('interviewer_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('scheduled_at');
            $table->string('location')->nullable(); // Room or Link
            $table->text('feedback')->nullable();
            $table->integer('rating')->nullable(); // 1-5
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_interviews');
        Schema::dropIfExists('hr_candidates');
        Schema::dropIfExists('hr_job_postings');
    }
};
