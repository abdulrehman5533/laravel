<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pos_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_sale_id')->index();
            $table->unsignedBigInteger('pos_customer_id')->nullable()->index();
            $table->string('payment_method')->default('cash');
            $table->decimal('amount', 16, 2)->default(0);
            $table->string('currency', 8)->default('PKR');
            $table->decimal('exchange_rate', 16, 6)->default(1);
            $table->string('reference')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('pos_sale_id')->references('id')->on('pos_sales')->onDelete('cascade');
            $table->foreign('pos_customer_id')->references('id')->on('pos_customers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_payments');
    }
};
