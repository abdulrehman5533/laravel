<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceShift extends Model
{
    protected $fillable = [
        'branch_id', 'name', 'start_time', 'end_time', 'grace_period', 'is_active'
    ];

    public function branch() { return $this->belongsTo(Branch::class); }
}
