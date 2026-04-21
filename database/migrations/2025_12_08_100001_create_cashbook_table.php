<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('date');
            $table->string('entry_type'); // 'cash_in' or 'cash_out'
            $table->string('category'); // Sales, Purchase, Expense, Refund, etc.
            $table->string('subcategory')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('reference_type')->nullable(); // sale, purchase, expense, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->string('payment_method')->default('cash'); // cash, cheque, online, etc.
            $table->string('status')->default('pending'); // pending, verified, cancelled
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'date']);
            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashbooks');
    }
};
