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
        Schema::table('installment_plans', function (Blueprint $table) {
            $table->integer('grace_period_days')->default(0)->after('late_fee_percentage');
            $table->string('late_fee_type')->default('percentage')->after('grace_period_days'); // percentage, fixed
            $table->decimal('fixed_late_fee_amount', 12, 2)->default(0)->after('late_fee_type');
        });

        Schema::table('installments', function (Blueprint $table) {
            $table->decimal('down_payment_amount', 12, 2)->default(0)->after('total_amount');
            $table->text('internal_notes')->nullable()->after('status');
        });

        Schema::table('installment_schedules', function (Blueprint $table) {
            $table->timestamp('reminder_sent_at')->nullable()->after('notes');
            $table->boolean('late_fee_applied')->default(false)->after('late_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('installment_plans', function (Blueprint $table) {
            $table->dropColumn(['grace_period_days', 'late_fee_type', 'fixed_late_fee_amount']);
        });

        Schema::table('installments', function (Blueprint $table) {
            $table->dropColumn(['down_payment_amount', 'internal_notes']);
        });

        Schema::table('installment_schedules', function (Blueprint $table) {
            $table->dropColumn(['reminder_sent_at', 'late_fee_applied']);
        });
    }
};
