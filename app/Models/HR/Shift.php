<?php

namespace App\Models\HR;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use BelongsToTenant;

    protected $table = 'hr_shifts';

    protected $fillable = [
        'tenant_id',
        'name',
        'start_time',
        'end_time',
        'grace_period_minutes',
        'is_night_shift',
        'color_code',
    ];

    public function employeeShifts(): HasMany
    {
        return $this->hasMany(EmployeeShift::class, 'shift_id');
    }
}
