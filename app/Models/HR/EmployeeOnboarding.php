<?php

namespace App\Models\HR;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeOnboarding extends Model
{
    protected $table = 'hr_employee_onboarding';

    protected $fillable = ['employee_id', 'checklist_id', 'status', 'started_at', 'completed_at'];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(OnboardingChecklist::class, 'checklist_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingTask::class, 'onboarding_id');
    }
}
