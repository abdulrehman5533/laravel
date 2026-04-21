<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transfer_items', function (Blueprint $table) {
            $table->decimal('received_quantity', 15, 4)->nullable()->after('quantity');
        });

        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->string('otp_code', 6)->nullable()->after('status');
            $table->boolean('is_otp_verified')->default(false)->after('otp_code');
            $table->boolean('discrepancy_found')->default(false)->after('is_otp_verified');
            $table->text('discrepancy_notes')->nullable()->after('discrepancy_found');
            $table->string('waybill_number')->nullable()->after('discrepancy_notes');
            $table->string('security_seal_number')->nullable()->after('waybill_number');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transfer_items', function (Blueprint $table) {
            $table->dropColumn('received_quantity');
        });

        Schema::table('inventory_transfers', function (Blueprint $table) {
            $table->dropColumn(['otp_code', 'is_otp_verified', 'discrepancy_found', 'discrepancy_notes', 'waybill_number', 'security_seal_number']);
        });
    }
};
