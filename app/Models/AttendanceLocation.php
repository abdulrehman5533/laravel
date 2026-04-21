<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLocation extends Model
{
    protected $fillable = [
        'branch_id', 'name', 'lat', 'lng', 'radius', 'is_active'
    ];

    public function branch() { return $this->belongsTo(Branch::class); }
}
