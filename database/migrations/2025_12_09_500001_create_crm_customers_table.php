<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crm_customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->unique();
            $table->string('alternate_phone', 20)->nullable();
            $table->string('cnic', 20)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode', 10);
            $table->enum('customer_type', ['retail', 'wholesale', 'VIP', 'agent'])->default('retail');
            $table->enum('vip_tier', ['none', 'silver', 'gold', 'platinum', 'diamond'])->default('none');
            $table->date('vip_since')->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('current_credit_used', 15, 2)->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->integer('total_purchases')->default(0);
            $table->decimal('average_order_value', 15, 2)->default(0);
            $table->string('birthstone')->nullable();
            $table->text('preferences')->nullable();
            $table->text('metal_preference')->nullable();
            $table->text('stone_preference')->nullable();
            $table->text('design_preference')->nullable();
            $table->decimal('loyalty_points', 12, 2)->default(0);
            $table->enum('communication_preference', ['whatsapp', 'sms', 'email', 'call', 'all'])->default('all');
            $table->boolean('whatsapp_consent')->default(false);
            $table->boolean('sms_consent')->default(false);
            $table->boolean('email_consent')->default(false);
            $table->string('whatsapp_number')->nullable();
            $table->text('repair_history')->nullable();
            $table->integer('repair_count')->default(0);
            $table->decimal('total_repair_amount', 15, 2)->default(0);
            $table->decimal('customer_rating', 3, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'blocked', 'blacklisted'])->default('active');
            $table->timestamp('last_purchase_date')->nullable();
            $table->timestamp('last_contact_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            $table->index('phone');
            $table->index('customer_type');
            $table->index('vip_tier');
            $table->index('status');
            $table->index(['customer_type', 'vip_tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crm_customers');
    }
};
