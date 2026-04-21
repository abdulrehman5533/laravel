<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingChecklist extends Model
{
    protected $table = 'hr_onboarding_checklists';

    protected $fillable = ['name', 'department', 'is_active'];

    public function tasks(): HasMany
    {
        return $this->hasMany(OnboardingTask::class, 'checklist_id');
    }
}
