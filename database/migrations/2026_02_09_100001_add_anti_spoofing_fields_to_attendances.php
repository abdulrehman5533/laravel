<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('device_integrity')->nullable()->after('device_id');
            $table->boolean('location_spoofed')->default(false)->after('device_integrity');
            $table->json('offline_payload')->nullable()->after('is_offline');
            $table->string('spoof_reason')->nullable()->after('location_spoofed');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['device_integrity', 'location_spoofed', 'offline_payload', 'spoof_reason']);
        });
    }
};
