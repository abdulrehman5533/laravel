<?php

namespace App\Models\HR;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOnboardingTask extends Model
{
    protected $table = 'hr_employee_onboarding_tasks';

    protected $fillable = ['onboarding_id', 'task_id', 'is_completed', 'completed_at', 'completed_by'];

    protected $casts = [
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    public function onboarding(): BelongsTo
    {
        return $this->belongsTo(EmployeeOnboarding::class, 'onboarding_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(OnboardingTask::class, 'task_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }
}
