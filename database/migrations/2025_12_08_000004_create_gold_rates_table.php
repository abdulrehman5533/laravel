<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gold_rates', function (Blueprint $table) {
            $table->id();
            $table->decimal('rate_22k', 10, 2);
            $table->decimal('rate_18k', 10, 2);
            $table->decimal('rate_24k', 10, 2);
            $table->decimal('silver_rate', 10, 2);
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gold_rates');
    }
};
