<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique();
            $table->string('account_name');
            $table->string('account_type'); // Asset, Liability, Equity, Revenue, Expense
            $table->string('account_category'); // Bank, Cash, Receivable, Payable, etc.
            $table->foreignId('parent_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['account_type']);
            $table->index(['parent_account_id']);
        });

        Schema::create('general_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->date('date');
            $table->string('entry_type'); // journal_entry, auto_entry
            $table->string('reference_type')->nullable(); // sale, purchase, expense, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->decimal('running_balance', 12, 2);
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['account_id', 'date']);
            $table->index(['date']);
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->date('entry_date');
            $table->text('narration');
            $table->string('status')->default('draft'); // draft, posted, cancelled
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('journal_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->decimal('debit', 12, 2)->default(0);
            $table->decimal('credit', 12, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_items');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('general_ledgers');
        Schema::dropIfExists('chart_of_accounts');
    }
};
