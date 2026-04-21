<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBom extends Model
{
    use BelongsToTenant, HasAuditLog;
    use SoftDeletes;

    protected $fillable = [
        'bom_number', 'name', 'description', 'inventory_product_id',
        'expected_gross_weight', 'expected_net_weight', 'allowed_wastage_percentage',
        'purity_id', 'estimated_labor_cost', 'labor_type', 'is_active', 'created_by',
    ];

    protected $casts = [
        'expected_gross_weight' => 'decimal:4',
        'expected_net_weight' => 'decimal:4',
        'allowed_wastage_percentage' => 'decimal:2',
        'estimated_labor_cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'inventory_product_id');
    }

    public function purity(): BelongsTo
    {
        return $this->belongsTo(PurityLevel::class, 'purity_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProductBomItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function calculateTotalEstimatedCost(): float
    {
        $itemCost = $this->items()->sum('estimated_cost');

        return (float) ($itemCost + $this->estimated_labor_cost);
    }
}
