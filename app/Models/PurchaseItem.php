<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseItem extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'purchase_order_id', 'product_id', 'item_code', 'stamp', 'description',
        'material_type', 'is_pata', 'pata_weight', 'unit', 'purity', 'quantity', 'unit_price',
        'gross_weight', 'less_weight', 'net_weight', 'tunch', 'wastage', 'fine', 'labour_charge',
        'line_amount', 'quantity_received', 'quantity_returned',
        'quantity_remaining', 'quality_status', 'quality_remarks',
        'weight_variance', 'purity_variance', 'batch_number',
        'serial_number', 'specifications', 'gst_percentage', 'gst_amount',
        'tunch_testing_status', 'is_approved',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'gross_weight' => 'decimal:4',
        'less_weight' => 'decimal:4',
        'net_weight' => 'decimal:4',
        'tunch' => 'decimal:4',
        'wastage' => 'decimal:4',
        'fine' => 'decimal:4',
        'labour_charge' => 'decimal:2',
        'line_amount' => 'decimal:2',
        'quantity_received' => 'decimal:4',
        'quantity_returned' => 'decimal:4',
        'quantity_remaining' => 'decimal:4',
        'weight_variance' => 'decimal:4',
        'purity_variance' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'is_approved' => 'boolean',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('quality_status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('quality_status', 'Pending');
    }

    public function scopeRejected($query)
    {
        return $query->where('quality_status', 'Rejected');
    }

    // Methods
    public function calculateLineAmount()
    {
        $this->line_amount = $this->quantity * $this->unit_price;
        $this->gst_amount = ($this->line_amount * $this->gst_percentage) / 100;
        $this->save();
    }

    public function updateQuantityRemaining()
    {
        $this->quantity_remaining = $this->quantity - $this->quantity_received - $this->quantity_returned;
        $this->save();
    }

    public function getIsVarianceItem()
    {
        return $this->weight_variance != 0 || $this->purity_variance != 0;
    }
}
