<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petty_cash', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->decimal('limit', 12, 2);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('petty_cash_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petty_cash_id')->constrained('petty_cash')->onDelete('cascade');
            $table->date('date');
            $table->string('type'); // disbursement, reimbursement
            $table->string('category'); // food, transport, stationery, etc.
            $table->decimal('amount', 12, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['petty_cash_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_entries');
        Schema::dropIfExists('petty_cash');
    }
};
