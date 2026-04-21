<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_category_wastages', function (Blueprint $table) {
            $table->id();
            $table->string('category_name'); // e.g., Ring, Bangle, Chain
            $table->string('metal_type')->default('Gold');
            $table->decimal('max_allowed_wastage_percent', 5, 2); // e.g., 3.50%
            $table->decimal('max_allowed_wastage_fixed', 10, 3)->nullable(); // grams
            $table->boolean('requires_admin_approval')->default(true);
            $table->timestamps();

            $table->unique(['category_name', 'metal_type']);
        });

        Schema::table('service_job_items', function (Blueprint $table) {
            $table->unsignedBigInteger('wastage_category_id')->nullable()->after('service_job_id');
            $table->decimal('wastage_limit_applied', 10, 3)->nullable()->after('wastage_allowed');
            $table->boolean('is_excess_wastage')->default(false)->after('wastage_actual');
            $table->foreign('wastage_category_id')->references('id')->on('service_category_wastages');
        });
    }

    public function down(): void
    {
        Schema::table('service_job_items', function (Blueprint $table) {
            $table->dropForeign(['wastage_category_id']);
            $table->dropColumn(['wastage_category_id', 'wastage_limit_applied', 'is_excess_wastage']);
        });
        Schema::dropIfExists('service_category_wastages');
    }
};
