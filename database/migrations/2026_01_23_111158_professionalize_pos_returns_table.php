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
        Schema::table('pos_returns_repairs', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_returns_repairs', 'return_no')) {
                $table->string('return_no')->unique()->after('id')->nullable();
            }
            if (! Schema::hasColumn('pos_returns_repairs', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('return_no')->constrained('branches');
            }
            if (! Schema::hasColumn('pos_returns_repairs', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('branch_id')->constrained('customers');
            }
            if (! Schema::hasColumn('pos_returns_repairs', 'refund_method')) {
                $table->string('refund_method')->nullable()->after('refund_amount');
            }
            if (! Schema::hasColumn('pos_returns_repairs', 'quantity_returned')) {
                $table->decimal('quantity_returned', 16, 3)->default(0)->after('pos_sale_item_id');
            }
            if (! Schema::hasColumn('pos_returns_repairs', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->after('status')->constrained('users');
            }
            if (! Schema::hasColumn('pos_returns_repairs', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('processed_by')->constrained('users');
            }
            if (! Schema::hasColumn('pos_returns_repairs', 'notes')) {
                $table->text('notes')->nullable()->after('reason');
            }
            if (! Schema::hasColumn('pos_returns_repairs', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_returns_repairs', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['customer_id']);
            $table->dropForeign(['processed_by']);
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'return_no', 'branch_id', 'customer_id', 'refund_method',
                'quantity_returned', 'processed_by', 'approved_by', 'notes', 'deleted_at',
            ]);
        });
    }
};
