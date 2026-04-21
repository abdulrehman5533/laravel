<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances');
            $table->foreignId('user_id')->constrained();
            $table->string('action'); // check-in, check-out, override, attempt, etc.
            $table->json('data')->nullable(); // store snapshot of data
            $table->string('ip_address')->nullable();
            $table->string('device_info')->nullable();
            $table->string('location_info')->nullable();
            $table->string('status')->nullable(); // success, failed, etc.
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_audit_logs');
    }
};
