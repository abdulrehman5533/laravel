<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceAuditLog extends Model
{
    protected $fillable = [
        'attendance_id', 'user_id', 'action', 'data', 'ip_address', 'device_info', 'location_info', 'status', 'remarks'
    ];

    public function attendance() { return $this->belongsTo(Attendance::class); }
    public function user() { return $this->belongsTo(User::class); }
}
