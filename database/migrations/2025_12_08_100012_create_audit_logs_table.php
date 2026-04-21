<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('action'); // create, update, delete, view, export
            $table->string('module'); // cashbook, expense, ledger, etc.
            $table->string('entity_type'); // Expense, Cashbook, Ledger, etc.
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['module', 'action']);
            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('period_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('period_type'); // month, quarter, year
            $table->string('status')->default('open'); // open, locked
            $table->foreignId('locked_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('locked_at')->nullable();
            $table->text('lock_reason')->nullable();
            $table->timestamps();

            $table->unique(['branch_id', 'start_date', 'end_date']);
        });

        Schema::create('transaction_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('entity_type'); // Expense, PettyCash, JournalEntry, etc.
            $table->unsignedBigInteger('entity_id');
            $table->string('action'); // approve, reject
            $table->text('remarks')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'entity_type']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_approvals');
        Schema::dropIfExists('period_locks');
        Schema::dropIfExists('audit_logs');
    }
};
