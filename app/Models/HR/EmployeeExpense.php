<?php

namespace App\Models\HR;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeExpense extends Model
{
    protected $table = 'hr_employee_expenses';

    protected $fillable = [
        'employee_id',
        'title',
        'amount',
        'expense_date',
        'category',
        'description',
        'receipt_path',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
