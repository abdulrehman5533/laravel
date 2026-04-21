<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('branch_id')->constrained();
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->timestamp('check_in_time')->nullable();
            $table->decimal('check_in_lat', 10, 7)->nullable();
            $table->decimal('check_in_lng', 10, 7)->nullable();
            $table->string('check_in_address')->nullable();
            $table->string('check_in_device')->nullable();
            $table->ipAddress('check_in_ip')->nullable();
            $table->timestamp('check_out_time')->nullable();
            $table->decimal('check_out_lat', 10, 7)->nullable();
            $table->decimal('check_out_lng', 10, 7)->nullable();
            $table->string('check_out_address')->nullable();
            $table->string('check_out_device')->nullable();
            $table->ipAddress('check_out_ip')->nullable();
            $table->integer('total_working_seconds')->nullable();
            $table->boolean('late_flag')->default(false);
            $table->boolean('early_leave_flag')->default(false);
            $table->enum('status', ['present', 'absent', 'on_leave', 'manual'])->default('present');
            $table->string('source')->nullable();
            $table->string('device_id')->nullable();
            $table->boolean('is_offline')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
};
