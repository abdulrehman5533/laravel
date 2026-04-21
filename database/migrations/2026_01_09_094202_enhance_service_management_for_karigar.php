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
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->foreignId('karigar_id')->nullable()->after('customer_id')->constrained('suppliers')->onDelete('set null');
            $table->string('priority')->default('medium')->after('job_number'); // low, medium, high, urgent
            $table->string('job_type')->default('repair')->after('service_type'); // repair, manufacturing, custom
            $table->text('karigar_instructions')->nullable()->after('special_instructions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropForeign(['karigar_id']);
            $table->dropColumn(['karigar_id', 'priority', 'job_type', 'karigar_instructions']);
        });
    }
};
