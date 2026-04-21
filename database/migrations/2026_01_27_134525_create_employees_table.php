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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('branch_id')->constrained();
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('national_id')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('department');
            $table->string('designation');
            $table->date('joining_date');
            $table->date('probation_end_date')->nullable();
            $table->date('resignation_date')->nullable();
            $table->decimal('base_salary', 15, 2)->default(0);
            $table->decimal('commission_percentage', 5, 2)->default(0);
            $table->boolean('is_karigar')->default(false);
            $table->enum('status', ['active', 'inactive', 'on_leave', 'terminated'])->default('active');
            $table->json('emergency_contact')->nullable();
            $table->json('bank_details')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
