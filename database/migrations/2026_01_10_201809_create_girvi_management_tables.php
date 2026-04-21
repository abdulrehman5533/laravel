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
        // 1. Main Girvi Master Table
        Schema::create('girvis', function (Blueprint $table) {
            $table->id();
            $table->string('girvi_number')->unique();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('branch_id')->constrained('branches');

            // Loan Details
            $table->decimal('loan_amount', 15, 2);
            $table->decimal('interest_rate', 5, 2); // Annual %
            $table->enum('interest_cycle', ['daily', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->enum('interest_type', ['simple', 'compound'])->default('simple');

            // Gold Rate Lock
            $table->decimal('locked_gold_rate', 15, 2); // Rate at creation
            $table->decimal('locked_silver_rate', 15, 2)->nullable();

            // Status & Dates
            $table->date('girvi_date');
            $table->date('maturity_date')->nullable();
            $table->enum('status', ['active', 'settled', 'overdue', 'transferred', 'auctioned'])->default('active');

            // Financial Balances
            $table->decimal('principal_paid', 15, 2)->default(0);
            $table->decimal('interest_accrued', 15, 2)->default(0);
            $table->decimal('interest_paid', 15, 2)->default(0);
            $table->decimal('penalty_amount', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2); // Calculated: Principal + Interest - Paid

            // Compliance & Risk
            $table->boolean('kyc_verified')->default(false);
            $table->string('risk_score')->nullable(); // High, Medium, Low
            $table->decimal('ltv_ratio', 5, 2)->nullable(); // Loan to Value ratio

            // Metadata
            $table->text('internal_notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'status']);
            $table->index('girvi_number');
        });

        // 2. Girvi Pledged Items
        Schema::create('girvi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('girvi_id')->constrained('girvis')->onDelete('cascade');
            $table->string('item_name');
            $table->string('item_type'); // Gold, Silver, Diamond
            $table->decimal('gross_weight', 10, 3);
            $table->decimal('net_weight', 10, 3);
            $table->string('purity');
            $table->decimal('valuation_rate', 15, 2);
            $table->decimal('estimated_value', 15, 2);
            $table->string('item_photo')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Auto Interest Posting Log
        Schema::create('girvi_interest_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('girvi_id')->constrained('girvis')->onDelete('cascade');
            $table->date('posting_date');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('interest_amount', 15, 2);
            $table->decimal('principal_balance_at_posting', 15, 2);
            $table->boolean('is_manual')->default(false);
            $table->foreignId('posted_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 4. Communication/Reminders Log
        Schema::create('girvi_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('girvi_id')->constrained('girvis')->onDelete('cascade');
            $table->enum('channel', ['sms', 'whatsapp', 'email', 'call']);
            $table->string('reminder_type'); // due_soon, overdue, maturity
            $table->string('recipient_contact');
            $table->text('message_content');
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered'])->default('pending');
            $table->string('gateway_response')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // 5. Girvi Payments & Settlements
        Schema::create('girvi_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('girvi_id')->constrained('girvis')->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->decimal('principal_component', 15, 2)->default(0);
            $table->decimal('interest_component', 15, 2)->default(0);
            $table->decimal('penalty_component', 15, 2)->default(0);
            $table->decimal('waiver_amount', 15, 2)->default(0);
            $table->string('payment_method'); // Cash, Bank, UPI
            $table->string('reference_number')->nullable();
            $table->foreignId('received_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('girvi_payments');
        Schema::dropIfExists('girvi_reminders');
        Schema::dropIfExists('girvi_interest_postings');
        Schema::dropIfExists('girvi_items');
        Schema::dropIfExists('girvis');
    }
};
