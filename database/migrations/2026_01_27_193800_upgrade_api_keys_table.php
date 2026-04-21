<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->string('secret', 128)->after('key')->nullable();
            $table->timestamp('expires_at')->after('status')->nullable();
            $table->json('ip_whitelist')->after('expires_at')->nullable();
            $table->integer('rate_limit')->default(1000)->after('ip_whitelist'); // per hour
        });
    }

    public function down(): void
    {
        Schema::table('api_keys', function (Blueprint $table) {
            $table->dropColumn(['secret', 'expires_at', 'ip_whitelist', 'rate_limit']);
        });
    }
};
