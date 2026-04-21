<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('pos_customer_id')->nullable()->index();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->dateTime('sale_time')->nullable();
            $table->decimal('subtotal', 16, 2)->default(0);
            $table->decimal('making_charges', 16, 2)->default(0);
            $table->decimal('wastage_amount', 16, 2)->default(0);
            $table->decimal('discount', 16, 2)->default(0);
            $table->decimal('tax_amount', 16, 2)->default(0);
            $table->decimal('total', 16, 2)->default(0);
            $table->string('currency', 8)->default('PKR');
            $table->decimal('exchange_rate', 16, 6)->default(1);
            $table->string('status')->default('open'); // open, held, completed, cancelled, returned
            $table->boolean('is_wholesale')->default(false);
            $table->json('payment_summary')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('pos_customer_id')->references('id')->on('pos_customers')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_sales');
    }
};
