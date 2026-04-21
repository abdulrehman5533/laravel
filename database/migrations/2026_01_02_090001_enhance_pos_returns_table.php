<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pos_returns_repairs', function (Blueprint $table) {
            if (Schema::hasColumn('pos_returns_repairs', 'return_date') === false) {
                $table->dateTime('return_date')->nullable()->after('type');
            }
            if (Schema::hasColumn('pos_returns_repairs', 'notes') === false) {
                $table->text('notes')->nullable()->after('reason');
            }
            if (Schema::hasColumn('pos_returns_repairs', 'approved_by') === false) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('notes');
            }
            if (Schema::hasColumn('pos_returns_repairs', 'processed_by') === false) {
                $table->unsignedBigInteger('processed_by')->nullable()->after('approved_by');
            }
            if (Schema::hasColumn('pos_returns_repairs', 'refund_method') === false) {
                $table->string('refund_method')->nullable()->after('refund_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('pos_returns_repairs', function (Blueprint $table) {
            $columns = ['return_date', 'notes', 'approved_by', 'processed_by', 'refund_method'];

            foreach ($columns as $column) {
                if (Schema::hasColumn('pos_returns_repairs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
