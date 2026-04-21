<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierCreditLimit extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'supplier_id', 'branch_id', 'approved_credit_limit',
        'current_credit_used', 'available_credit', 'credit_days',
        'credit_type', 'overdue_amount', 'days_overdue', 'has_overdue',
        'status', 'effective_from', 'effective_to', 'calculation_frequency_days',
        'last_calculated_at', 'requires_approval', 'approved_by', 'approved_at',
        'notes', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'last_calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'approved_credit_limit' => 'decimal:2',
        'current_credit_used' => 'decimal:2',
        'available_credit' => 'decimal:2',
        'overdue_amount' => 'decimal:2',
        'has_overdue' => 'boolean',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
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
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'Blocked');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'Suspended');
    }

    public function scopeWithOverdue($query)
    {
        return $query->where('has_overdue', true);
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeExpiringSoon($query)
    {
        return $query->whereNotNull('effective_to')
            ->where('effective_to', '<=', now()->addDays(7));
    }

    // Methods
    public function updateCreditUsage($amount)
    {
        $this->current_credit_used += $amount;
        $this->available_credit = $this->approved_credit_limit - $this->current_credit_used;

        if ($this->current_credit_used > $this->approved_credit_limit) {
            $this->status = 'Suspended';
        }

        $this->save();
    }

    public function reduceCreditUsage($amount)
    {
        $this->current_credit_used = max(0, $this->current_credit_used - $amount);
        $this->available_credit = $this->approved_credit_limit - $this->current_credit_used;
        $this->save();
    }

    public function updateOverdueStatus($amount, $days = null)
    {
        $this->overdue_amount = $amount;
        $this->days_overdue = $days ?? $this->days_overdue;
        $this->has_overdue = $amount > 0;

        if ($this->has_overdue) {
            $this->status = 'Suspended';
        }

        $this->save();
    }

    public function clearOverdue()
    {
        $this->overdue_amount = 0;
        $this->days_overdue = 0;
        $this->has_overdue = false;
        $this->status = 'Active';
        $this->save();
    }

    public function canUtilizeCredit($amount)
    {
        return $this->status === 'Active' &&
               ($this->available_credit >= $amount);
    }

    public function isExceedingLimit()
    {
        return $this->current_credit_used > $this->approved_credit_limit;
    }

    public function getUtilizationPercentage()
    {
        return ($this->current_credit_used / $this->approved_credit_limit) * 100;
    }
}
