<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceJobWorkflow extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'service_job_id', 'step_number', 'workflow_step', 'description',
        'started_at', 'completed_at', 'duration_minutes', 'notes',
        'assigned_to', 'photos',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'photos' => 'json',
    ];

    // Relationships
    public function serviceJob()
    {
        return $this->belongsTo(ServiceJob::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Methods
    public function isCompleted()
    {
        return $this->completed_at !== null;
    }

    public function getDurationInHours()
    {
        if ($this->duration_minutes) {
            return round($this->duration_minutes / 60, 2);
        }

        return null;
    }

    public function getWorkflowLabel()
    {
        return match ($this->workflow_step) {
            'received' => 'Item Received',
            'checking' => 'Quality Checking',
            'workshop' => 'Workshop Processing',
            'polishing' => 'Polishing & Finishing',
            'completed' => 'Completed',
            default => $this->workflow_step,
        };
    }

    public function markComplete()
    {
        $this->completed_at = now();
        if ($this->started_at) {
            $this->duration_minutes = $this->started_at->diffInMinutes($this->completed_at);
        }

        return $this;
    }
}
