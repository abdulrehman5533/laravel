<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseReconciliationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'reconciliation_id',
        'product_id',
        'system_quantity',
        'physical_quantity',
        'discrepancy',
        'adjustment_action',
        'is_adjusted',
    ];

    protected $casts = [
        'system_quantity' => 'decimal:4',
        'physical_quantity' => 'decimal:4',
        'discrepancy' => 'decimal:4',
        'is_adjusted' => 'boolean',
    ];

    public function reconciliation()
    {
        return $this->belongsTo(WarehouseReconciliation::class, 'reconciliation_id');
    }

    public function product()
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }
}
