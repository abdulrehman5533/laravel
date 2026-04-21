<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_key_id')->constrained('api_keys')->onDelete('cascade');
            $table->string('endpoint');
            $table->string('method');
            $table->integer('status_code');
            $table->float('response_time_ms');
            $table->string('ip_address');
            $table->timestamps();
        });

        Schema::create('api_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('event_type'); // e.g., sale.created, order.approved
            $table->string('secret')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('tenant_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::table('api_keys', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->json('scopes')->nullable()->after('ip_whitelist');
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'scopes']);
        });
        Schema::dropIfExists('api_webhooks');
        Schema::dropIfExists('api_usage_logs');
    }
};
