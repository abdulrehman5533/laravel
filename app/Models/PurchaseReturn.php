<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseReturn extends Model
{
    use BelongsToTenant, HasAuditLog;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'purchase_order_id', 'supplier_id', 'return_number',
        'return_date', 'return_reason', 'return_reason_details', 'subtotal',
        'gst_percentage', 'gst_amount', 'return_amount', 'refund_type',
        'refund_date', 'refund_processed', 'refund_amount', 'status',
        'approved', 'approved_by', 'approved_at', 'notes', 'reference_number',
        'created_by', 'updated_by',
    ];

    protected $casts = [
        'return_date' => 'date',
        'refund_date' => 'date',
        'refund_processed' => 'boolean',
        'approved' => 'boolean',
        'approved_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'return_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Initiated');
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopeRefunded($query)
    {
        return $query->where('refund_processed', true);
    }

    public function scopeByReason($query, $reason)
    {
        return $query->where('return_reason', $reason);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeUnprocessed($query)
    {
        return $query->where('refund_processed', false);
    }

    // Methods
    public function approve($userId)
    {
        $this->approved = true;
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->status = 'Approved';
        $this->save();
    }

    public function processRefund($amount = null)
    {
        $this->refund_amount = $amount ?? $this->return_amount;
        $this->refund_date = now();
        $this->refund_processed = true;
        $this->status = 'Processed';
        $this->save();
    }

    public function calculateGST()
    {
        $this->gst_amount = ($this->subtotal * $this->gst_percentage) / 100;
        $this->return_amount = $this->subtotal + $this->gst_amount;
        $this->save();
    }
}
