<?php

namespace App\Models\HR;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryAction extends Model
{
    protected $table = 'hr_disciplinary_actions';

    protected $fillable = [
        'employee_id',
        'action_type',
        'incident_date',
        'reason',
        'action_taken',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
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
