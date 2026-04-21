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
        // 1. Add KYC fields to customers
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'id_type')) {
                $table->string('id_type')->nullable()->after('phone');
                $table->string('id_number')->nullable()->after('id_type');
                $table->string('id_photo_front')->nullable()->after('id_number');
                $table->string('id_photo_back')->nullable()->after('id_photo_front');
                $table->string('customer_photo')->nullable()->after('id_photo_back');
                $table->string('occupation')->nullable()->after('customer_photo');
                $table->string('emergency_contact_name')->nullable()->after('occupation');
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }
        });

        // 2. Add Approval and Loan details to girvis
        Schema::table('girvis', function (Blueprint $table) {
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending')->after('status');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('loan_purpose')->nullable()->after('loan_amount');
            $table->string('guarantor_name')->nullable()->after('loan_purpose');
            $table->string('guarantor_phone')->nullable()->after('guarantor_name');
            $table->string('guarantor_id_type')->nullable()->after('guarantor_phone');
            $table->string('guarantor_id_number')->nullable()->after('guarantor_id_type');
        });

        // 3. Add Storage tracking to girvi_items
        Schema::table('girvi_items', function (Blueprint $table) {
            $table->string('locker_location')->nullable()->after('item_photo');
            $table->string('bag_number')->nullable()->after('locker_location');
            $table->string('box_number')->nullable()->after('bag_number');
            $table->string('tag_number')->nullable()->after('box_number');
            $table->boolean('is_physically_verified')->default(false)->after('tag_number');
            $table->timestamp('last_verified_at')->nullable()->after('is_physically_verified');
            $table->foreignId('verified_by')->nullable()->constrained('users')->after('last_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'id_type', 'id_number', 'id_photo_front', 'id_photo_back', 
                'customer_photo', 'occupation', 'emergency_contact_name', 'emergency_contact_phone'
            ]);
        });

        Schema::table('girvis', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn([
                'approval_status', 'approved_by', 'approved_at', 
                'loan_purpose', 'guarantor_name', 'guarantor_phone', 
                'guarantor_id_type', 'guarantor_id_number'
            ]);
        });

        Schema::table('girvi_items', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn([
                'locker_location', 'bag_number', 'box_number', 
                'tag_number', 'is_physically_verified', 'last_verified_at', 'verified_by'
            ]);
        });
    }
};
