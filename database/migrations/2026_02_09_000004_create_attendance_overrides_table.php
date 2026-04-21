<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendance_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances');
            $table->foreignId('admin_id')->constrained('users');
            $table->string('action'); // manual_check_in, manual_check_out, edit, delete
            $table->json('data')->nullable();
            $table->string('reason');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendance_overrides');
    }
};
