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
        Schema::create('girvi_auctions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('girvi_id');
            $table->date('auction_date');
            $table->decimal('reserve_price', 15, 2);
            $table->decimal('final_bid_amount', 15, 2)->nullable();
            $table->string('winning_bidder_name')->nullable();
            $table->string('winning_bidder_contact')->nullable();
            $table->decimal('auction_charges', 15, 2)->default(0);
            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled, failed
            $table->text('auctioneer_notes')->nullable();

            // Financial Settlement
            $table->decimal('principal_recovered', 15, 2)->default(0);
            $table->decimal('interest_recovered', 15, 2)->default(0);
            $table->decimal('surplus_amount', 15, 2)->default(0); // Amount to be returned to customer
            $table->string('surplus_status')->default('pending'); // pending, paid_to_customer, escheated

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('girvi_id')->references('id')->on('girvis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('girvi_auctions');
    }
};
