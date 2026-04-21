<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceOverride extends Model
{
    protected $fillable = [
        'attendance_id', 'admin_id', 'action', 'data', 'reason', 'status'
    ];

    public function attendance() { return $this->belongsTo(Attendance::class); }
    public function admin() { return $this->belongsTo(User::class, 'admin_id'); }
}
