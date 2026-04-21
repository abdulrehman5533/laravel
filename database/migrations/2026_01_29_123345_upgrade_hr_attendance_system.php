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
        Schema::table('hr_attendance', function (Blueprint $table) {
            $table->string('gps_in')->nullable()->after('location_coords');
            $table->string('gps_out')->nullable()->after('gps_in');
            $table->string('selfie_in_path')->nullable()->after('gps_out');
            $table->string('selfie_out_path')->nullable()->after('selfie_in_path');
            $table->string('device_id')->nullable()->after('selfie_out_path');
            $table->string('ip_address')->nullable()->after('device_id');
            $table->boolean('is_verified')->default(false)->after('status');
            $table->string('verification_method')->nullable()->after('is_verified'); // selfie, gps, biometric, manual
            $table->foreignId('verified_by')->nullable()->constrained('users')->after('verification_method');
            $table->text('notes')->nullable()->after('verified_by');

            // Jewellery specific: tracking production job association
            $table->foreignId('production_job_id')->nullable()->constrained('production_jobs')->after('notes');
        });

        Schema::create('hr_attendance_corrections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained('hr_attendance')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->time('requested_clock_in')->nullable();
            $table->time('requested_clock_out')->nullable();
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hr_overtime_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained('hr_attendance')->onDelete('cascade');
            $table->date('date');
            $table->integer('minutes');
            $table->decimal('rate_per_hour', 15, 2)->nullable();
            $table->decimal('calculated_amount', 15, 2)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_overtime_logs');
        Schema::dropIfExists('hr_attendance_corrections');
        Schema::table('hr_attendance', function (Blueprint $table) {
            $table->dropForeign(['production_job_id']);
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'gps_in', 'gps_out', 'selfie_in_path', 'selfie_out_path',
                'device_id', 'ip_address', 'is_verified', 'verification_method',
                'verified_by', 'notes', 'production_job_id',
            ]);
        });
    }
};
