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
        Schema::create('service_job_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_job_id')->constrained('service_jobs')->onDelete('cascade');
            $table->string('ornament_name');
            $table->string('metal_type'); // Gold, Silver, Platinum
            $table->decimal('purity_expected', 8, 2)->nullable();
            $table->decimal('purity_received', 8, 2)->nullable();
            $table->decimal('weight_issued', 12, 3)->nullable();
            $table->decimal('weight_received', 12, 3)->nullable();
            $table->decimal('wastage_allowed', 12, 3)->default(0);
            $table->decimal('wastage_actual', 12, 3)->nullable();
            $table->decimal('labor_charge', 15, 2)->default(0);
            $table->string('barcode')->unique()->nullable();
            $table->string('status')->default('issued'); // issued, in_job, received, approved
            $table->text('quality_remarks')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_job_items');
    }
};
