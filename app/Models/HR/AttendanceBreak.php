<?php

namespace App\Models\HR;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceBreak extends Model
{
    protected $table = 'hr_attendance_breaks';

    protected $fillable = [
        'attendance_id',
        'break_type',
        'break_in',
        'break_out',
        'duration_minutes',
        'notes',
    ];

    protected $casts = [
        'break_in' => 'datetime',
        'break_out' => 'datetime',
    ];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}
