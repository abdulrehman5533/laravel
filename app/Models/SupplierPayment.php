<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierPayment extends Model
{
    use BelongsToTenant, HasAuditLog;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'supplier_id', 'purchase_order_id', 'payment_reference',
        'payment_date', 'amount_paid', 'payment_method', 'metal_type', 'metal_weight',
        'metal_rate', 'is_bhav_cut', 'cheque_number',
        'cheque_date', 'cheque_status', 'transaction_id', 'bank_reference',
        'principal_amount', 'gst_amount', 'discount_applied', 'interest_charged',
        'status', 'approved', 'approved_by', 'approved_at', 'invoice_reference',
        'notes', 'attachment_path', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'cheque_date' => 'date',
        'approved' => 'boolean',
        'approved_at' => 'datetime',
        'amount_paid' => 'decimal:2',
        'principal_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'discount_applied' => 'decimal:2',
        'interest_charged' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
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

    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'Confirmed');
    }

    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByChequeStatus($query, $status)
    {
        return $query->where('cheque_status', $status);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('payment_date', [$from, $to]);
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

    public function markAsFailed()
    {
        $this->status = 'Failed';
        $this->save();
    }

    public function reversePayment()
    {
        $this->status = 'Reversed';
        $this->save();
    }

    public function updateChequeStatus($status)
    {
        $this->cheque_status = $status;
        if ($status === 'Cleared') {
            $this->status = 'Approved';
        } elseif ($status === 'Bounced') {
            $this->status = 'Failed';
        }
        $this->save();
    }

    public function getIsOverdueAttribute()
    {
        return $this->cheque_date && now()->isAfter($this->cheque_date) &&
               $this->cheque_status === 'Pending';
    }
}
