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
        Schema::table('installment_schedules', function (Blueprint $table) {
            $table->string('customer_email')->nullable()->after('due_date');
            $table->dateTime('last_reminder_sent_at')->nullable()->after('paid_date');
            $table->integer('reminder_count')->default(0)->after('last_reminder_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installment_schedules', function (Blueprint $table) {
            $table->dropColumn(['customer_email', 'last_reminder_sent_at', 'reminder_count']);
        });
    }
};
