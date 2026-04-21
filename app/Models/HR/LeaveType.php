<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $table = 'hr_leave_types';

    protected $fillable = [
        'name',
        'code',
        'annual_allocation',
        'is_paid',
        'carry_forward',
        'max_carry_forward',
    ];

    public function requests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_id');
    }
}
