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
        Schema::create('karigar_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->foreignId('supplier_id')->constrained();
            $table->string('settlement_number')->unique();
            $table->date('date');
            $table->enum('metal_type', ['Gold', 'Silver', 'Platinum', 'Other'])->default('Gold');
            $table->decimal('fine_weight_fixed', 15, 3)->default(0);
            $table->decimal('fixed_rate', 15, 2)->default(0);
            $table->decimal('metal_value', 15, 2)->default(0);
            $table->decimal('labor_amount', 15, 2)->default(0);
            $table->decimal('other_charges', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('payment_status')->default('Pending'); // Pending, Partially Paid, Paid
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karigar_settlements');
    }
};
