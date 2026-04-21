<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeLoan extends Model
{
    protected $table = 'hr_employee_loans';

    protected $fillable = [
        'employee_id',
        'amount',
        'interest_rate',
        'repayment_months',
        'monthly_installment',
        'remaining_balance',
        'disbursement_date',
        'status',
    ];

    protected $casts = [
        'disbursement_date' => 'date',
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'monthly_installment' => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
