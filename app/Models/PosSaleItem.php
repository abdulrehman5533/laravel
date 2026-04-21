<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PosSaleItem extends Model
{
    use BelongsToTenant, HasAuditLog;
    use HasFactory, SoftDeletes;

    protected $table = 'pos_sale_items';

    protected $fillable = [
        'pos_sale_id', 'product_id', 'sku', 'description', 'quantity', 'unit', 'unit_price', 'weight', 'gross_weight', 'stone_weight', 'net_weight', 'fine_weight', 'gold_rate', 'gold_purity',
        'stone_count', 'stone_carat', 'stone_type', 'stone_cut', 'stone_clarity', 'stone_color', 'stone_price', 'stone_price_per_carat', 'stone_certification', 'certificate_no',
        'making_charge', 'making_charge_type', 'making_charge_amount', 'wastage_percent', 'wastage_weight', 'wastage_amount', 'melting_loss', 'tax_percent', 'tax_amount', 'discount_percent', 'discount_amount', 'line_total', 'meta',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'weight' => 'decimal:4',
        'gross_weight' => 'decimal:4',
        'stone_weight' => 'decimal:4',
        'net_weight' => 'decimal:4',
        'fine_weight' => 'decimal:4',
        'gold_rate' => 'decimal:2',
        'stone_carat' => 'decimal:4',
        'stone_price' => 'decimal:2',
        'making_charge' => 'decimal:2',
        'making_charge_amount' => 'decimal:2',
        'wastage_percent' => 'decimal:3',
        'wastage_amount' => 'decimal:2',
        'tax_percent' => 'decimal:3',
        'tax_amount' => 'decimal:2',
        'discount_percent' => 'decimal:3',
        'discount_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
        'stone_count' => 'integer',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($item) {
            // Avoid infinite loop if we are already calculating
            if (! $item->is_calculating) {
                $item->is_calculating = true;
                $item->calculateLineTotals();
                $item->is_calculating = false;
            }
        });
    }

    protected $is_calculating = false;

    public function sale()
    {
        return $this->belongsTo(PosSale::class, 'pos_sale_id');
    }

    public function calculateLineTotals(): void
    {
        $qty = (float) $this->quantity;
        $unitPrice = (float) $this->unit_price;
        $isWholesale = $this->sale ? $this->sale->is_wholesale : false;

        // Weight calculations
        $grossWeight = (float) $this->gross_weight;
        $stoneWeight = (float) $this->stone_weight;
        $netWeight = $grossWeight > 0 ? ($grossWeight - $stoneWeight) : (float) $this->weight;

        // Purity extraction for fine weight calculation
        $purityPercentage = 100;
        if ($this->gold_purity) {
            preg_match('/(\d+\.?\d*)/', $this->gold_purity, $matches);
            if (isset($matches[1])) {
                $val = (float) $matches[1];
                $purityPercentage = ($val <= 24) ? ($val / 24 * 100) : $val;
            }
        }

        // Enterprise Wastage Logic: Weight-based wastage
        $wastageWeight = (float) $this->wastage_weight;
        if ($wastageWeight <= 0 && $this->wastage_percent > 0) {
            $wastageWeight = $netWeight * ($this->wastage_percent / 100);
        }

        $chargeableWeight = $netWeight + $wastageWeight;
        $fineWeight = $chargeableWeight * ($purityPercentage / 100);

        // Making charge calculation
        $baseMakingCharge = (float) $this->making_charge;
        $totalMakingCharge = 0;

        switch ($this->making_charge_type) {
            case 'per_gram':
                $totalMakingCharge = $baseMakingCharge * $netWeight;
                break;
            case 'per_piece':
                $totalMakingCharge = $baseMakingCharge * $qty;
                break;
            case 'fixed':
            default:
                $totalMakingCharge = $baseMakingCharge;
                break;
        }

        // Gold Price logic
        $goldPrice = 0;
        if ($isWholesale) {
            // Wholesale typically bills based on Fine Weight or Net Weight + Fixed Margin
            // If gold_rate is present, it's used; otherwise, it might be a metal-to-metal transfer (handled in accounting)
            if ($this->gold_rate > 0) {
                $goldPrice = $chargeableWeight * (float) $this->gold_rate;
            }
        } else {
            // Retail billing
            if ($chargeableWeight > 0 && $this->gold_rate > 0) {
                $goldPrice = $chargeableWeight * (float) $this->gold_rate;
            }
        }

        // Stone price calculation
        $stonePrice = (float) $this->stone_price;
        if ($stonePrice <= 0 && $this->stone_price_per_carat > 0) {
            $stonePrice = $this->stone_price_per_carat * (float) $this->stone_carat;
        }

        // Subtotal before tax and discount
        // If unitPrice is set (Retail/Item based), it takes precedence for the base item value
        $baseItemValue = ($unitPrice > 0) ? ($qty * $unitPrice) : 0;
        $subtotal = $baseItemValue + $goldPrice + $totalMakingCharge + $stonePrice;

        // Apply item discount
        $itemDiscount = 0;
        if ($this->discount_percent > 0) {
            $itemDiscount = ($subtotal * $this->discount_percent) / 100;
        } elseif ($this->discount_amount > 0) {
            $itemDiscount = $this->discount_amount;
        }

        $subtotalAfterDiscount = $subtotal - $itemDiscount;

        // Calculate tax
        $tax = ($this->tax_percent / 100) * $subtotalAfterDiscount;

        // Final line total
        $lineTotal = $subtotalAfterDiscount + $tax;

        $this->update([
            'net_weight' => round($netWeight, 4),
            'weight' => round($netWeight, 4),
            'fine_weight' => round($fineWeight, 4),
            'wastage_weight' => round($wastageWeight, 4),
            'wastage_amount' => round($wastageWeight * ($this->gold_rate ?: 0), 2),
            'making_charge_amount' => round($totalMakingCharge, 2),
            'tax_amount' => round($tax, 2),
            'discount_amount' => round($itemDiscount, 2),
            'line_total' => round($lineTotal, 2),
        ]);

        if ($this->pos_sale_id) {
            $this->sale->recalculateTotals();
        }
    }

    /**
     * Get the inventory product associated with this sale item
     */
    public function inventoryProduct()
    {
        return $this->belongsTo(\App\Models\InventoryProduct::class, 'product_id');
    }

    /**
     * Get formatted gold price
     */
    public function getGoldPriceAttribute()
    {
        return $this->weight * $this->gold_rate;
    }

    /**
     * Get total weight for this item
     */
    public function getTotalWeightAttribute()
    {
        return $this->weight * $this->quantity;
    }
}
