<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodLock extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'branch_id', 'start_date', 'end_date', 'period_type',
        'status', 'locked_by', 'locked_at', 'lock_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'locked_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }
}
