<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name', 'module', 'steps', 'is_active'];

    protected $casts = [
        'steps' => 'json',
        'is_active' => 'boolean',
    ];

    public function approvals(): HasMany
    {
        return $this->hasMany(WorkflowApproval::class);
    }

    public function stepsTable(): HasMany
    {
        return $this->hasMany(WorkflowStep::class, 'workflow_id')->orderBy('step_order');
    }
}
