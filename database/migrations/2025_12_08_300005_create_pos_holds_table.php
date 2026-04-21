<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pos_holds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pos_sale_id')->nullable()->index();
            $table->string('hold_reference')->nullable();
            $table->unsignedBigInteger('held_by')->nullable()->index();
            $table->json('sale_snapshot');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->foreign('pos_sale_id')->references('id')->on('pos_sales')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_holds');
    }
};
