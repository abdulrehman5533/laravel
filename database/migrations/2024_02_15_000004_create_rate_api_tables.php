<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rate API Providers
        Schema::create('rate_api_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('name');
            $table->string('api_url');
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('update_frequency')->default(60);
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('tenant_id');
            $table->index('is_active');
        });

        // Rate API Logs
        Schema::create('rate_api_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('rate_api_providers')->onDelete('cascade');
            $table->enum('status', ['success', 'failed', 'timeout'])->default('failed');
            $table->integer('response_code')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('records_updated')->default(0);
            $table->integer('execution_time_ms')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index('provider_id');
            $table->index('status');
            $table->index('created_at');
        });

        // Rate API Mappings
        Schema::create('rate_api_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('rate_api_providers')->onDelete('cascade');
            $table->string('metal_type');
            $table->string('purity');
            $table->string('api_field_name');
            $table->string('local_field_name');
            $table->timestamp('created_at')->nullable();
            $table->index('provider_id');
        });

        // Historical Rate API Data
        Schema::create('historical_rate_api_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('rate_api_providers')->onDelete('cascade');
            $table->string('metal_type');
            $table->string('purity');
            $table->decimal('rate', 15, 4);
            $table->string('currency')->default('INR');
            $table->timestamp('fetched_at');
            $table->timestamp('created_at')->nullable();
            $table->index('provider_id');
            $table->index('metal_type');
            $table->index('fetched_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_rate_api_data');
        Schema::dropIfExists('rate_api_mappings');
        Schema::dropIfExists('rate_api_logs');
        Schema::dropIfExists('rate_api_providers');
    }
};
