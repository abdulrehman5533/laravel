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
            $table->decimal('rate_21k', 15, 2)->after('rate_22k')->nullable();
            $table->decimal('rate_20k', 15, 2)->after('rate_21k')->nullable();
            $table->decimal('rate_14k', 15, 2)->after('rate_18k')->nullable();

            // Purchase / Buying Rates (Old Gold)
            $table->decimal('buy_rate_24k', 15, 2)->nullable();
            $table->decimal('buy_rate_22k', 15, 2)->nullable();
            $table->decimal('buy_rate_21k', 15, 2)->nullable();
            $table->decimal('buy_silver_rate', 15, 2)->nullable();

            $table->boolean('is_locked')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');

            // Adding a comment for better understanding
            $table->string('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gold_rates', function (Blueprint $table) {
            $table->dropColumn([
                'rate_21k', 'rate_20k', 'rate_14k',
                'buy_rate_24k', 'buy_rate_22k', 'buy_rate_21k', 'buy_silver_rate',
                'is_locked', 'created_by', 'updated_by', 'comment',
            ]);
        });
    }
};
