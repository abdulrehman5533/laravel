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
        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->onDelete('set null');
            $table->string('mapping_key')->nullable()->unique()->after('account_code'); // e.g., 'sales_gold', 'ar_customers'
        });

        Schema::table('general_ledgers', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->onDelete('set null');
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->onDelete('set null');
            $table->string('currency', 3)->default('PKR')->after('narration');
            $table->decimal('exchange_rate', 10, 6)->default(1.0)->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropColumn(['branch_id', 'currency', 'exchange_rate']);
        });

        Schema::table('general_ledgers', function (Blueprint $table) {
            $table->dropColumn(['branch_id']);
        });

        Schema::table('chart_of_accounts', function (Blueprint $table) {
            $table->dropColumn(['branch_id', 'mapping_key']);
        });
    }
};
