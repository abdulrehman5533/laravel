<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasAuditLog, BelongsToTenant;

    protected $table = 'hr_payroll';

    protected $fillable = [
        'tenant_id',
        'employee_id',
        'month_year',
        'basic_salary',
        'allowances',
        'deductions',
        'loan_deduction',
        'expense_reimbursement',
        'commission',
        'bonus_performance',
        'karigar_making_charges',
        'sales_commission',
        'overtime_pay',
        'tax_amount',
        'late_deduction',
        'social_security_contribution',
        'net_salary',
        'compliance_details',
        'salary_breakdown',
        'payment_status',
        'paid_on',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'loan_deduction' => 'decimal:2',
        'expense_reimbursement' => 'decimal:2',
        'commission' => 'decimal:2',
        'bonus_performance' => 'decimal:2',
        'karigar_making_charges' => 'decimal:2',
        'sales_commission' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'late_deduction' => 'decimal:2',
        'social_security_contribution' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'compliance_details' => 'array',
        'salary_breakdown' => 'array',
        'paid_on' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
