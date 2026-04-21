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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()->after('user_id')->constrained('users')->onDelete('set null');
            }
            if (Schema::hasColumn('employees', 'department')) {
                $table->string('department')->nullable()->change();
            }
        });

        Schema::table('hr_leave_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_leave_requests', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }
            if (!Schema::hasColumn('hr_leave_requests', 'is_half_day')) {
                $table->boolean('is_half_day')->default(false)->after('days_taken');
            }
            if (!Schema::hasColumn('hr_leave_requests', 'half_day_type')) {
                $table->enum('half_day_type', ['first_half', 'second_half'])->nullable()->after('is_half_day');
            }
            if (!Schema::hasColumn('hr_leave_requests', 'line_manager_approved')) {
                $table->boolean('line_manager_approved')->default(false)->after('status');
            }
            if (!Schema::hasColumn('hr_leave_requests', 'line_manager_id')) {
                $table->foreignId('line_manager_id')->nullable()->after('line_manager_approved')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('hr_leave_requests', 'line_manager_approved_at')) {
                $table->timestamp('line_manager_approved_at')->nullable()->after('line_manager_id');
            }
            if (!Schema::hasColumn('hr_leave_requests', 'hr_approved')) {
                $table->boolean('hr_approved')->default(false)->after('line_manager_approved_at');
            }
            if (!Schema::hasColumn('hr_leave_requests', 'document_path')) {
                $table->string('document_path')->nullable()->after('reason');
            }
        });

        Schema::table('hr_leave_types', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_leave_types', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }
        });
        
        Schema::table('hr_shifts', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_shifts', 'tenant_id')) {
                $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_shifts', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('hr_leave_types', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('hr_leave_requests', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['line_manager_id']);
            $table->dropColumn([
                'tenant_id', 'is_half_day', 'half_day_type', 'line_manager_approved', 
                'line_manager_id', 'line_manager_approved_at', 'hr_approved', 'document_path'
            ]);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn('manager_id');
        });
    }
};
