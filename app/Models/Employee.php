<?php

namespace App\Models;

use App\Models\HR\Appraisal;
use App\Models\HR\AttendanceCorrection;
use App\Models\HR\EmployeeDocument;
use App\Models\HR\EmployeeExpense;
use App\Models\HR\EmployeeLeaveBalance;
use App\Models\HR\EmployeeLoan;
use App\Models\HR\EmployeeSalaryStructure;
use App\Models\HR\EmployeeShift;
use App\Models\HR\EmployeeTraining;
use App\Models\HR\KarigarRate;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LifecycleEvent;
use App\Models\HR\OvertimeLog;
use App\Models\HR\SalesCommissionRate;
use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'branch_id',
        'employee_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'address',
        'national_id',
        'tax_id',
        'department',
        'designation',
        'joining_date',
        'probation_end_date',
        'resignation_date',
        'base_salary',
        'hra',
        'medical_allowance',
        'transport_allowance',
        'eobi_deduction',
        'pessi_deduction',
        'income_tax',
        'commission_percentage',
        'is_karigar',
        'status',
        'performance_score',
        'skills',
        'onboarding_status',
        'country_code',
        'biometric_id',
        'attendance_pin',
        'emergency_contact',
        'bank_details',
    ];

    protected $casts = [
        'emergency_contact' => 'json',
        'bank_details' => 'json',
        'skills' => 'json',
        'performance_score' => 'decimal:2',
        'date_of_birth' => 'date',
        'joining_date' => 'date',
        'probation_end_date' => 'date',
        'resignation_date' => 'date',
        'base_salary' => 'decimal:2',
        'hra' => 'decimal:2',
        'medical_allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'eobi_deduction' => 'decimal:2',
        'pessi_deduction' => 'decimal:2',
        'income_tax' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'is_karigar' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendances(): HasMany
    {
        // attendances table uses user_id, not employee_id
        return $this->hasMany(Attendance::class, 'user_id', 'user_id');
    }

    public function leaveApplications(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(EmployeeLeaveBalance::class);
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(Appraisal::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function lifecycleEvents(): HasMany
    {
        return $this->hasMany(LifecycleEvent::class);
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(EmployeeTraining::class);
    }

    public function loans(): HasMany
    {
        return $this->hasMany(EmployeeLoan::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(EmployeeExpense::class);
    }

    public function salaryStructures(): HasMany
    {
        return $this->hasMany(EmployeeSalaryStructure::class);
    }

    public function karigarRates(): HasMany
    {
        return $this->hasMany(KarigarRate::class);
    }

    public function salesCommissionRates(): HasMany
    {
        return $this->hasMany(SalesCommissionRate::class);
    }

    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function overtimeLogs(): HasMany
    {
        return $this->hasMany(OvertimeLog::class);
    }

    public function getCurrentShiftAttribute()
    {
        return $this->shifts()
            ->where('start_date', '<=', now()->toDateString())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now()->toDateString());
            })
            ->latest('start_date')
            ->first()?->shift;
    }

    public function assignShift($shiftId, $startDate, $endDate = null)
    {
        // Deactivate current active shift if any
        if ($endDate === null) {
            $this->shifts()
                ->whereNull('end_date')
                ->update(['end_date' => \Carbon\Carbon::parse($startDate)->subDay()->toDateString()]);
        }

        return $this->shifts()->create([
            'tenant_id' => $this->tenant_id,
            'shift_id' => $shiftId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
