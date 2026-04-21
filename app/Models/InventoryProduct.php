<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class InventoryProduct extends Model
{
    use BelongsToTenant;
    use HasAuditLog, HasFactory, SoftDeletes;

    protected $table = 'inventory_products';

    protected $fillable = [
        'sku', 'tag_id', 'batch_no', 'stamp', 'serial_number', 'name', 'gender', 'description', 'category_id', 'collection',
        'purity_id', 'metal_color', 'branch_id',
        'weight', 'gross_weight', 'net_weight', 'fine_weight', 'wastage_percentage', 'unit', 'stone_count', 'stone_carat', 'stone_type',
        'length', 'width', 'height', 'size',
        'barcode', 'qr_code', 'image_path', 'cost_price', 'selling_price', 'wholesale_price', 'wholesale_margin_percentage', 'price_slabs', 'use_margin_pricing', 'valuation_method', 'tax_rate',
        'making_charge_type', 'making_charge_value', 'labor_charge', 'hallmark', 'is_hallmarked', 'certificate_no',
        'current_stock', 'current_pieces', 'reorder_level', 'reorder_quantity', 'status',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'weight' => 'decimal:4',
        'gross_weight' => 'decimal:4',
        'net_weight' => 'decimal:4',
        'fine_weight' => 'decimal:4',
        'wastage_percentage' => 'decimal:2',
        'stone_carat' => 'decimal:4',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'wholesale_margin_percentage' => 'decimal:2',
        'price_slabs' => 'array',
        'use_margin_pricing' => 'boolean',
        'tax_rate' => 'decimal:2',
        'making_charge_value' => 'decimal:2',
        'labor_charge' => 'decimal:2',
        'is_hallmarked' => 'boolean',
        'current_stock' => 'decimal:4',
        'current_pieces' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relations
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function purity(): BelongsTo
    {
        return $this->belongsTo(PurityLevel::class, 'purity_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function stoneAttributes(): HasMany
    {
        return $this->hasMany(StoneAttribute::class, 'product_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public function wastageRecords(): HasMany
    {
        return $this->hasMany(WastageTracking::class, 'product_id');
    }

    public function damageRecords(): HasMany
    {
        return $this->hasMany(DamageRepairTracking::class, 'product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(InventoryProductImage::class, 'product_id')->orderBy('sort_order');
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(StockAlert::class, 'product_id');
    }

    public function intelligence(): HasOne
    {
        return $this->hasOne(InventoryProductIntelligence::class, 'product_id');
    }

    public function transferItems(): HasMany
    {
        return $this->hasMany(InventoryTransferItem::class, 'product_id');
    }

    public function girviItems(): HasMany
    {
        return $this->hasMany(GirviItem::class, 'inventory_product_id');
    }

    public function getIsPledgedAttribute()
    {
        return $this->girviItems()->whereHas('girvi', function ($q) {
            $q->where('status', 'active');
        })->exists();
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_stock', '<=', 'reorder_level');
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByPurity($query, $purityId)
    {
        return $query->where('purity_id', $purityId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSearchByName($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('sku', 'like', "%{$search}%");
    }

    // Accessors & Mutators
    public function getProfitMarginPercentAttribute()
    {
        if ($this->cost_price == 0) {
            return 0;
        }

        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    public function getStockStatusAttribute()
    {
        if ($this->current_stock <= 0) {
            return 'Out of Stock';
        }
        if ($this->current_stock <= $this->reorder_level) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    public function getTotalValueAttribute()
    {
        return $this->current_stock * $this->cost_price;
    }

    public function getProfitMarginAttribute()
    {
        if ($this->cost_price == 0) {
            return 0;
        }

        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    // Methods
    public function updateStock($quantity, $type, $reference = null, $weight = 0, $pieces = 0)
    {
        if ($type === 'add') {
            $this->current_stock += $quantity;
            $this->current_pieces += $pieces;
            $this->net_weight += $weight;
        } elseif ($type === 'subtract') {
            $this->current_stock -= $quantity;
            $this->current_pieces -= $pieces;
            $this->net_weight -= $weight;
        }

        $this->calculateFineWeight();
        $this->save();

        StockMovement::create([
            'product_id' => $this->id,
            'quantity' => $quantity,
            'weight' => $weight,
            'pieces' => $pieces,
            'type' => $type,
            'reference' => $reference,
            'created_by' => Auth::id(),
        ]);
    }

    public function calculateFineWeight()
    {
        $purityPercentage = $this->purity ? $this->purity->percentage : 100;
        $effectivePurity = $purityPercentage + ($this->wastage_percentage ?? 0);

        $this->fine_weight = ($this->net_weight * $effectivePurity) / 100;
    }

    /**
     * Tier-1 Valuation Logic: Metal + Stone + Making
     */
    public function calculateSellingPrice(float $currentMetalRate): float
    {
        if ($this->valuation_method === 'fixed') {
            return (float) $this->selling_price;
        }

        // 1. Metal Value
        $metalValue = ($this->net_weight * $currentMetalRate);

        // 2. Making Charges
        $makingCharges = 0;
        if ($this->making_charge_type === 'per_gram') {
            $makingCharges = $this->net_weight * $this->making_charge_value;
        } else {
            $makingCharges = $this->making_charge_value;
        }

        // 3. Stone Value
        $stoneValue = $this->stoneAttributes()->sum('total_stone_value');

        return (float) ($metalValue + $makingCharges + $stoneValue);
    }

    public function calculateWholesalePrice(float $currentMetalRate, int $quantity = 1): float
    {
        if ($this->use_margin_pricing && $this->cost_price > 0) {
            $marginAmount = ($this->cost_price * $this->wholesale_margin_percentage) / 100;
            $basePrice = $this->cost_price + $marginAmount;
        } else {
            $basePrice = $this->wholesale_price ?: $this->calculateSellingPrice($currentMetalRate);
        }

        // Apply Price Slabs
        $applicableDiscount = 0;
        if (! empty($this->price_slabs)) {
            $slabs = collect($this->price_slabs)->sortByDesc('qty');
            foreach ($slabs as $slab) {
                if ($quantity >= $slab['qty']) {
                    $applicableDiscount = $slab['discount'];
                    break;
                }
            }
        }

        return $basePrice * (1 - ($applicableDiscount / 100));
    }

    public function recordWastage($quantity, $reason, $notes = null, $weight = 0, $pieces = 0)
    {
        // If weight not provided, assume quantity is the weight
        $w = $weight ?: $quantity;
        $this->updateStock($quantity, 'subtract', 'wastage', $w, $pieces);

        return WastageTracking::create([
            'product_id' => $this->id,
            'quantity' => $quantity,
            'reason' => $reason,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    public function recordDamage($quantity, $status, $notes = null, $weight = 0, $pieces = 0)
    {
        // For discarded items, we definitely subtract from stock
        // For others, it depends on business logic, but usually we move it out of sellable stock
        $w = $weight ?: $quantity;
        $this->updateStock($quantity, 'subtract', 'damage', $w, $pieces);

        return DamageRepairTracking::create([
            'product_id' => $this->id,
            'quantity' => $quantity,
            'status' => $status,
            'notes' => $notes,
            'created_by' => Auth::id(),
        ]);
    }

    public function getAgeInDays()
    {
        return now()->diffInDays($this->created_at);
    }
}
