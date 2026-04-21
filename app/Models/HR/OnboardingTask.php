<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingTask extends Model
{
    protected $table = 'hr_onboarding_tasks';

    protected $fillable = ['checklist_id', 'task_name', 'description', 'sort_order'];

    public function checklist(): BelongsTo
    {
        return $this->belongsTo(OnboardingChecklist::class, 'checklist_id');
    }
}
