<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pos_returns_repairs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_sale_id')->nullable()->index();
            $table->unsignedBigInteger('pos_sale_item_id')->nullable()->index();
            $table->string('type')->default('return'); // return, exchange, repair
            $table->string('reason')->nullable();
            $table->decimal('refund_amount', 16, 2)->default(0);
            $table->string('status')->default('initiated'); // initiated, approved, processed, rejected
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('pos_sale_id')->references('id')->on('pos_sales')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_returns_repairs');
    }
};
