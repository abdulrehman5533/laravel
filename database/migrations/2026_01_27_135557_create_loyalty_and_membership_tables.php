<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Silver, Gold, Platinum
            $table->integer('min_points');
            $table->decimal('multiplier', 5, 2)->default(1.00); // 1.2x points for VIP
            $table->json('benefits')->nullable();
            $table->timestamps();
        });

        Schema::create('loyalty_points_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained();
            $table->integer('points');
            $table->string('transaction_type'); // earned, redeemed, expired
            $table->string('source_type')->nullable(); // Sale, Referral
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'loyalty_tier_id')) {
                $table->foreignId('loyalty_tier_id')->nullable()->constrained('loyalty_tiers');
                $table->integer('total_loyalty_points')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['loyalty_tier_id']);
            $table->dropColumn(['loyalty_tier_id', 'total_loyalty_points']);
        });
        Schema::dropIfExists('loyalty_points_ledger');
        Schema::dropIfExists('loyalty_tiers');
    }
};
