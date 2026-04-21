<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pos_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('name');
            $table->string('phone')->nullable()->index();
            $table->string('email')->nullable()->unique();
            $table->string('customer_type')->default('regular'); // regular, vip, loyalty
            $table->integer('loyalty_points')->default(0);
            $table->decimal('credit_limit', 16, 2)->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pos_customers');
    }
};
