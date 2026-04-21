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
        Schema::table('gold_rates', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('comment');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->timestamp('published_at')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gold_rates', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_by', 'approved_at', 'published_at']);
        });
    }
};
