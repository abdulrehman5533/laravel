<?php

namespace App\Models\HR;

use App\Models\Branch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Holiday extends Model
{
    use BelongsToTenant;

    protected $table = 'hr_holidays';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'date',
        'is_optional',
        'is_market_holiday',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'is_optional' => 'boolean',
        'is_market_holiday' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
