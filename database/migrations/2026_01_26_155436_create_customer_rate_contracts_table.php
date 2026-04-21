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
        Schema::create('customer_rate_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('metal_type')->default('gold');
            $table->unsignedBigInteger('purity_id')->nullable();

            $table->enum('contract_type', ['fixed', 'discount_from_live', 'premium_on_live'])->default('discount_from_live');
            $table->decimal('value', 15, 2)->comment('Fixed rate or discount/premium value');

            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('reference_number')->nullable();
            $table->timestamps();

            $table->foreign('purity_id')->references('id')->on('purity_levels');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_rate_contracts');
    }
};
