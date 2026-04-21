<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->unique();
            $table->string('phone_primary');
            $table->string('phone_secondary')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code');
            $table->string('country')->default('Pakistan');

            // Supplier Details
            $table->string('gstin')->unique()->nullable();
            $table->string('pan')->unique()->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->enum('payment_terms', ['COD', 'NET_15', 'NET_30', 'NET_45', 'NET_60', 'CUSTOM'])->default('NET_30');
            $table->integer('payment_days')->default(30);

            // Supplier Category
            $table->enum('supplier_type', ['Precious_Metals', 'Gemstones', 'Diamonds', 'Accessories', 'Mixed'])->default('Mixed');
            $table->string('product_specialty')->nullable();

            // Rating & Performance
            $table->decimal('rating', 3, 2)->default(0)->comment('0-5 scale');
            $table->integer('quality_score')->default(0)->comment('0-100 scale');
            $table->integer('delivery_score')->default(0)->comment('0-100 scale');

            // Status & Compliance
            $table->enum('status', ['Active', 'Inactive', 'Blocked', 'OnHold'])->default('Active');
            $table->boolean('gst_registered')->default(false);
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('current_credit_used', 15, 2)->default(0);

            // Documentation
            $table->text('notes')->nullable();
            $table->string('registration_number')->nullable();
            $table->date('registration_date')->nullable();
            $table->integer('total_purchases')->default(0);
            $table->decimal('total_purchased_amount', 15, 2)->default(0);

            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('branch_id');
            $table->index('status');
            $table->index('supplier_type');
            $table->fullText(['name', 'contact_person', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
