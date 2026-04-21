<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    use BelongsToTenant;

    protected $table = 'workflow_steps';

    protected $fillable = [
        'workflow_id',
        'name',
        'step_order',
        'approver_type',
        'approver_id',
        'condition_type',
        'condition_value',
        'sla_hours',
        'can_edit_model',
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}
