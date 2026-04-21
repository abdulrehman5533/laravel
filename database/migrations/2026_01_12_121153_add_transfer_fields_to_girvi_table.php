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
        Schema::table('girvis', function (Blueprint $table) {
            $table->string('transfer_type')->nullable()->after('status'); // bank, third_party
            $table->string('transferred_to')->nullable()->after('transfer_type');
            $table->decimal('transfer_amount', 15, 2)->nullable()->after('transferred_to');
            $table->date('transfer_date')->nullable()->after('transfer_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('girvis', function (Blueprint $table) {
            $table->dropColumn(['transfer_type', 'transferred_to', 'transfer_amount', 'transfer_date']);
        });
    }
};
