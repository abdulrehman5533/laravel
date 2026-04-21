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
        Schema::table('hr_attendance_rules', function (Blueprint $table) {
            $table->boolean('require_gps')->default(false)->after('is_default');
            $table->boolean('require_selfie')->default(false)->after('require_gps');
            $table->string('allowed_ip_range')->nullable()->after('require_selfie');
            $table->integer('geofencing_radius_meters')->nullable()->after('allowed_ip_range');
            $table->decimal('office_latitude', 10, 8)->nullable()->after('geofencing_radius_meters');
            $table->decimal('office_longitude', 11, 8)->nullable()->after('office_latitude');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('attendance_pin', 6)->nullable()->after('biometric_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('attendance_pin');
        });

        Schema::table('hr_attendance_rules', function (Blueprint $table) {
            $table->dropColumn([
                'require_gps',
                'require_selfie',
                'allowed_ip_range',
                'geofencing_radius_meters',
                'office_latitude',
                'office_longitude'
            ]);
        });
    }
};
