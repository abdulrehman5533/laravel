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
        Schema::create('gold_savings_schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('scheme_name');
            $table->decimal('monthly_amount', 15, 2);
            $table->integer('duration_months');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('accumulated_amount', 15, 2)->default(0);
            $table->decimal('accumulated_weight', 10, 3)->default(0); // Gold weight equivalent
            $table->enum('status', ['active', 'completed', 'closed', 'matured'])->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gold_savings_schemes');
    }
};
