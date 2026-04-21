<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\HR\AttendanceBreak;
use App\Models\HR\AttendanceCorrection;
use App\Models\HR\OvertimeLog;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'branch_id',
        'shift_id',
        'check_in_time',
        'check_in_lat',
        'check_in_lng',
        'check_in_address',
        'check_in_device',
        'check_in_ip',
        'check_out_time',
        'check_out_lat',
        'check_out_lng',
        'check_out_address',
        'check_out_device',
        'check_out_ip',
        'total_working_seconds',
        'late_flag',
        'early_leave_flag',
        'status',
        'source',
        'device_id',
        'device_integrity',
        'location_spoofed',
        'spoof_reason',
        'is_offline',
        'offline_payload',
        'synced_at',
    ];

    protected $casts = [
        'check_in_time'   => 'datetime',
        'check_out_time'  => 'datetime',
        'synced_at'       => 'datetime',
        'late_flag'       => 'boolean',
        'early_leave_flag'=> 'boolean',
        'location_spoofed'=> 'boolean',
        'is_offline'      => 'boolean',
        'offline_payload' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo
    {
        // attendances links via user_id -> employees.user_id
        return $this->belongsTo(Employee::class, 'user_id', 'user_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(\App\Models\HR\Shift::class, 'shift_id');
    }

    public function getWorkingHoursAttribute(): float
    {
        return round(($this->total_working_seconds ?? 0) / 3600, 2);
    }
}
