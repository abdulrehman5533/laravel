<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pos_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_sale_id')->index();
            $table->string('format')->default('thermal'); // thermal, a4, whatsapp
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('pos_sale_id')->references('id')->on('pos_sales')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_invoices');
    }
};
