<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'location',
        'branch_id',
        'is_active',
        'iot_sensor_id',
        'rfid_enabled',
        'capacity_volume',
        'iot_thresholds',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rfid_enabled' => 'boolean',
        'capacity_volume' => 'decimal:2',
        'iot_thresholds' => 'json',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function bins(): HasMany
    {
        return $this->hasMany(WarehouseBin::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(InventoryProduct::class);
    }
}
