<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use BelongsToTenant, HasAuditLog;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'supplier_id', 'po_number', 'invoice_type', 'is_urd', 'po_date',
        'expected_delivery_date', 'actual_delivery_date', 'material_type',
        'description', 'sub_total', 'gst_percentage', 'gst_amount',
        'discount_percentage', 'discount_amount', 'other_charges',
        'other_charges_description', 'total_amount', 'amount_paid',
        'amount_due', 'status', 'payment_status', 'is_overdue',
        'days_overdue', 'received', 'received_date', 'quantity_received',
        'quantity_variance', 'quality_remarks', 'quality_status',
        'reference_number', 'notes', 'terms_conditions', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'received_date' => 'date',
        'received' => 'boolean',
        'is_overdue' => 'boolean',
    ];

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PurchasePriceHistory::class);
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
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['Cancelled']);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true);
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status', ['Unpaid', 'Partial', 'Overdue']);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->whereBetween('po_date', [$from, $to]);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('po_number', 'like', "%{$term}%")
            ->orWhere('reference_number', 'like', "%{$term}%")
            ->orWhereHas('supplier', function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            });
    }

    // Methods
    public function calculateDaysOverdue()
    {
        if ($this->expected_delivery_date && $this->status !== 'Received') {
            $days = now()->diffInDays($this->expected_delivery_date);
            $this->is_overdue = $days > 0;
            $this->days_overdue = max(0, $days);
            $this->save();
        }
    }

    public function markAsReceived($quantityReceived = null)
    {
        $this->received = true;
        $this->received_date = now();
        $this->status = 'Received';

        if ($quantityReceived) {
            $this->quantity_received = $quantityReceived;
            $this->quantity_variance = $this->sub_total - $quantityReceived;
        }

        $this->save();
    }

    public function recalculateTotals()
    {
        $this->sub_total = $this->items()->sum('line_amount');
        $this->gst_amount = ($this->sub_total * $this->gst_percentage) / 100;
        $this->discount_amount = ($this->sub_total * $this->discount_percentage) / 100;
        $this->total_amount = $this->sub_total + $this->gst_amount + $this->other_charges - $this->discount_amount;
        $this->amount_due = $this->total_amount - $this->amount_paid;
        $this->save();
    }

    public function isOverDueForecast()
    {
        return $this->expected_delivery_date &&
               now()->isAfter($this->expected_delivery_date) &&
               ! $this->received;
    }

    public function markAsApproved()
    {
        $this->status = 'Approved';
        $this->save();
    }

    public function markAsRejected($reason = null)
    {
        $this->status = 'Rejected';
        $this->notes .= "\nRejected: " . $reason;
        $this->save();
    }
}
