<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pos_pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('name');
            $table->decimal('discount_percent', 8, 3)->default(0);
            $table->json('conditions')->nullable(); // e.g., min_qty, customer_type
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_pricing_tiers');
    }
};
