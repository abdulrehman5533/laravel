<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GirviItem extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'girvi_id', 'inventory_product_id', 'vault_id', 'box_number', 'status',
        'item_name', 'item_type', 'gross_weight',
        'net_weight', 'stone_weight', 'fine_weight', 'purity',
        'valuation_rate', 'estimated_value', 'item_condition',
        'item_photo', 'barcode', 'qr_code', 'description',
        'locker_location', 'bag_number', 'tag_number', 
        'is_physically_verified', 'last_verified_at', 'verified_by',
    ];

    protected $casts = [
        'gross_weight' => 'decimal:3',
        'net_weight' => 'decimal:3',
        'stone_weight' => 'decimal:3',
        'fine_weight' => 'decimal:3',
        'valuation_rate' => 'decimal:2',
        'estimated_value' => 'decimal:2',
    ];

    public function girvi(): BelongsTo
    {
        return $this->belongsTo(Girvi::class);
    }

    public function vault(): BelongsTo
    {
        return $this->belongsTo(Vault::class);
    }

    public function inventoryProduct(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'inventory_product_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GirviItemPhoto::class, 'girvi_item_id');
    }
}
