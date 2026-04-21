<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buybacks', function (Blueprint $table) {
            $table->id();
            $table->string('buyback_number')->unique();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');

            $table->string('item_description');
            $table->enum('metal_type', ['Gold', 'Silver', 'Platinum']);
            $table->decimal('gross_weight', 10, 3);
            $table->decimal('stone_weight', 10, 3)->default(0);
            $table->decimal('net_weight', 10, 3);

            $table->decimal('purity_reported', 5, 2); // Customer claim
            $table->decimal('purity_tested', 5, 2);   // Lab/Touch test
            $table->decimal('melting_loss_expected', 10, 3)->default(0);
            $table->decimal('net_fine_weight', 10, 3);

            $table->decimal('rate_applied', 15, 2);
            $table->decimal('total_value', 15, 2);

            $table->enum('exchange_type', ['cash', 'exchange', 'account_credit']);
            $table->enum('status', ['draft', 'completed', 'sent_to_refinery', 'refined'])->default('draft');

            $table->string('refinery_batch_id')->nullable();
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buybacks');
    }
};
