<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowApproval extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'workflow_id',
        'step_id',
        'model_type',
        'model_id',
        'current_step',
        'approver_id',
        'actioned_by',
        'actioned_at',
        'status',
        'comments',
        'rejection_reason',
        'audit_trail',
        'sla_due_at',
        'is_escalated',
        'escalated_at',
    ];

    protected $casts = [
        'audit_trail' => 'json',
        'actioned_at' => 'datetime',
        'sla_due_at' => 'datetime',
        'escalated_at' => 'datetime',
        'is_escalated' => 'boolean',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'step_id');
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function model()
    {
        return $this->morphTo();
    }
}
