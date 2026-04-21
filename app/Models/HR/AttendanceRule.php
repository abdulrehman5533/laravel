<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRule extends Model
{
    use HasFactory;

    protected $table = 'hr_attendance_rules';

    protected $fillable = [
        'rule_name',
        'branch_id',
        'shift_start',
        'shift_end',
        'grace_time_minutes',
        'half_day_late_minutes',
        'ot_rate_multiplier',
        'require_gps',
        'require_selfie',
        'allowed_ip_range',
        'geofencing_radius_meters',
        'office_latitude',
        'office_longitude',
        'is_default',
        'weekly_off_days',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'require_gps' => 'boolean',
        'require_selfie' => 'boolean',
        'geofencing_radius_meters' => 'integer',
        'office_latitude' => 'decimal:8',
        'office_longitude' => 'decimal:8',
        'weekly_off_days' => 'array',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }
}
