<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'description',
        'budget_period',
        'start_date',
        'end_date',
        'status',
        'total_budget',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the branch
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get budget items
     */
    public function items(): HasMany
    {
        return $this->hasMany(BudgetItem::class);
    }

    /**
     * Get budget approvals
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(BudgetApproval::class);
    }

    /**
     * Get budget tracking
     */
    public function tracking(): HasMany
    {
        return $this->hasMany(BudgetTracking::class);
    }

    /**
     * Calculate total budgeted amount
     */
    public function getTotalBudgetedAmount(): float
    {
        return $this->items()->sum('budgeted_amount');
    }

    /**
     * Calculate total actual amount
     */
    public function getTotalActualAmount(): float
    {
        return $this->items()->sum('actual_amount');
    }

    /**
     * Calculate total variance
     */
    public function getTotalVariance(): float
    {
        return $this->getTotalBudgetedAmount() - $this->getTotalActualAmount();
    }

    /**
     * Calculate variance percentage
     */
    public function getVariancePercentage(): float
    {
        $budgeted = $this->getTotalBudgetedAmount();
        if ($budgeted == 0) {
            return 0;
        }
        return round(($this->getTotalVariance() / $budgeted) * 100, 2);
    }

    /**
     * Get budget status
     */
    public function getBudgetStatus(): string
    {
        $variance = $this->getVariancePercentage();
        
        if ($variance < -10) {
            return 'exceeded';
        } elseif ($variance < 0) {
            return 'warning';
        }
        
        return 'on_track';
    }

    /**
     * Check if budget is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved' && $this->approved_at !== null;
    }

    /**
     * Approve budget
     */
    public function approve(User $user, string $comments = null): bool
    {
        $this->status = 'approved';
        $this->approved_by = $user->id;
        $this->approved_at = now();
        $this->save();

        BudgetApproval::create([
            'budget_id' => $this->id,
            'approved_by' => $user->id,
            'approval_level' => 1,
            'status' => 'approved',
            'comments' => $comments,
            'approved_at' => now(),
        ]);

        return true;
    }

    /**
     * Reject budget
     */
    public function reject(User $user, string $comments = null): bool
    {
        $this->status = 'draft';
        $this->save();

        BudgetApproval::create([
            'budget_id' => $this->id,
            'approved_by' => $user->id,
            'approval_level' => 1,
            'status' => 'rejected',
            'comments' => $comments,
        ]);

        return true;
    }

    /**
     * Activate budget
     */
    public function activate(): bool
    {
        if (!$this->isApproved()) {
            return false;
        }

        $this->status = 'active';
        $this->save();

        return true;
    }

    /**
     * Close budget
     */
    public function close(): bool
    {
        $this->status = 'closed';
        $this->save();

        return true;
    }
}
