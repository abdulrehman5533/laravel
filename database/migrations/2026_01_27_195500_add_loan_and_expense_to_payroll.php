<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_payroll', function (Blueprint $table) {
            $table->decimal('loan_deduction', 15, 2)->default(0)->after('deductions');
            $table->decimal('expense_reimbursement', 15, 2)->default(0)->after('loan_deduction');
        });
    }

    public function down(): void
    {
        Schema::table('hr_payroll', function (Blueprint $table) {
            $table->dropColumn(['loan_deduction', 'expense_reimbursement']);
        });
    }
};
