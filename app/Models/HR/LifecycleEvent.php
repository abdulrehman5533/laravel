<?php

namespace App\Models\HR;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifecycleEvent extends Model
{
    protected $table = 'hr_lifecycle_events';

    protected $fillable = [
        'employee_id',
        'event_type',
        'effective_date',
        'description',
        'metadata',
        'recorded_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'metadata' => 'json',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
