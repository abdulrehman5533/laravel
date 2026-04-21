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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('first_name')->after('customer_code');
            $table->string('last_name')->after('first_name');
            $table->string('mobile')->nullable()->after('phone');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->string('address_line_1')->nullable()->after('address');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('city')->nullable()->after('address_line_2');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->string('country')->nullable()->after('postal_code');
            $table->string('customer_type')->default('individual')->after('country'); // individual, business
            $table->string('company_name')->nullable()->after('customer_type');
            $table->string('tax_id')->nullable()->after('company_name');
            $table->decimal('credit_limit', 15, 2)->default(0)->after('tax_id');
            $table->decimal('current_balance', 15, 2)->default(0)->after('credit_limit');
            $table->integer('loyalty_points')->default(0)->after('current_balance');
            $table->string('membership_level')->default('bronze')->after('loyalty_points'); // bronze, silver, gold, platinum
            $table->string('preferred_payment_method')->nullable()->after('membership_level');
            $table->text('notes')->nullable()->after('preferred_payment_method');
            $table->boolean('is_active')->default(true)->after('notes');
            $table->timestamp('last_purchase_date')->nullable()->after('is_active');
            $table->decimal('average_order_value', 15, 2)->default(0)->after('total_purchases');
            $table->string('referral_source')->nullable()->after('average_order_value');
            $table->boolean('marketing_consent')->default(false)->after('referral_source');
            $table->decimal('special_discount_percentage', 5, 2)->default(0)->after('marketing_consent');
            $table->foreignId('branch_id')->nullable()->after('special_discount_percentage')->constrained('branches')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->after('branch_id')->constrained('users')->onDelete('set null');
            if (!Schema::hasColumn('customers', 'deleted_at')) {
                $table->softDeletes();
            }

            // Remove the old 'name' column if you want, or keep it.
            // Better to keep it for compatibility if any code still uses it,
            // or migrate data from 'name' to 'first_name' and 'last_name' then drop it.
            // For now, I'll keep it to avoid breaking things immediately.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'first_name', 'last_name', 'mobile', 'gender', 'address_line_1',
                'address_line_2', 'city', 'state', 'postal_code', 'country',
                'customer_type', 'company_name', 'tax_id', 'credit_limit',
                'current_balance', 'loyalty_points', 'membership_level',
                'preferred_payment_method', 'notes', 'is_active',
                'last_purchase_date', 'average_order_value', 'referral_source',
                'marketing_consent', 'special_discount_percentage', 'branch_id', 'created_by',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
