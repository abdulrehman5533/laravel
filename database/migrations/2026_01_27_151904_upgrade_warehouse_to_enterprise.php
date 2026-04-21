<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('iot_sensor_id')->nullable();
            $table->boolean('rfid_enabled')->default(false);
            $table->decimal('capacity_volume', 15, 2)->nullable();
            $table->json('iot_thresholds')->nullable(); // Temperature, Humidity, etc.
        });

        Schema::create('warehouse_shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique();
            $table->foreignId('warehouse_id')->constrained();
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('status')->default('pending'); // pending, transit, delivered
            $table->timestamp('estimated_delivery')->nullable();
            $table->json('gps_tracking_logs')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_shipments');
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn(['iot_sensor_id', 'rfid_enabled', 'capacity_volume', 'iot_thresholds']);
        });
    }
};
