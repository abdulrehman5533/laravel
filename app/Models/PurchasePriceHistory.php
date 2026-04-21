<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasePriceHistory extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'supplier_id', 'product_id', 'item_code', 'item_name',
        'material_type', 'unit_price', 'unit', 'effective_from',
        'effective_to', 'price_change_percentage', 'purchase_order_id',
        'reference_number', 'is_current_price', 'purity', 'recorded_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'unit_price' => 'decimal:4',
        'price_change_percentage' => 'decimal:2',
        'is_current_price' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Scopes
    public function scopeCurrent($query)
    {
        return $query->where('is_current_price', true);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeByMaterialType($query, $type)
    {
        return $query->where('material_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', now());
            });
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('effective_from', [$from, $to]);
    }

    // Methods
    public function makeCurrentPrice()
    {
        // Mark all other prices as non-current
        static::where('supplier_id', $this->supplier_id)
            ->where('item_code', $this->item_code)
            ->update(['is_current_price' => false]);

        $this->is_current_price = true;
        $this->effective_to = null;
        $this->save();
    }

    public function calculatePriceChange($previousPrice)
    {
        if ($previousPrice > 0) {
            $this->price_change_percentage = (($this->unit_price - $previousPrice) / $previousPrice) * 100;
            $this->save();
        }
    }

    public function getPreviousPrice()
    {
        return static::where('supplier_id', $this->supplier_id)
            ->where('item_code', $this->item_code)
            ->where('effective_from', '<', $this->effective_from)
            ->orderByDesc('effective_from')
            ->first();
    }

    public function getNextPrice()
    {
        return static::where('supplier_id', $this->supplier_id)
            ->where('item_code', $this->item_code)
            ->where('effective_from', '>', $this->effective_from)
            ->orderBy('effective_from')
            ->first();
    }
}
