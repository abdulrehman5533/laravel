<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cashier_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('cashier_id')->constrained('users')->onDelete('cascade');
            $table->date('shift_date');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('total_cash_in', 12, 2)->default(0);
            $table->decimal('total_cash_out', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2)->nullable();
            $table->decimal('physical_count', 12, 2)->nullable();
            $table->decimal('variance', 12, 2)->nullable();
            $table->string('status')->default('open'); // open, closed, verified
            $table->text('notes')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'shift_date']);
            $table->index(['cashier_id', 'shift_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cashier_shifts');
    }
};
