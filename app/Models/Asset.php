<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'asset_code',
        'name',
        'branch_id',
        'category',
        'purchase_date',
        'purchase_cost',
        'current_value',
        'depreciation_rate',
        'depreciation_method',
        'maintenance_interval_days',
        'last_maintenance_date',
        'next_maintenance_date',
        'lifecycle_status',
        'maintenance_history',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'current_value' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'maintenance_history' => 'json',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
