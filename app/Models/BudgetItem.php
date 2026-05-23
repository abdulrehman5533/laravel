<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'budget_id',
        'chart_of_account_id',
        'category',
        'description',
        'budgeted_amount',
        'actual_amount',
        'variance',
        'variance_percentage',
        'status',
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'variance' => 'decimal:2',
        'variance_percentage' => 'decimal:2',
    ];

    /**
     * Get the budget
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Get the chart of account
     */
    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    /**
     * Get tracking records
     */
    public function tracking(): HasMany
    {
        return $this->hasMany(BudgetTracking::class);
    }

    /**
     * Calculate variance
     */
    public function calculateVariance(): void
    {
        $this->variance = $this->budgeted_amount - $this->actual_amount;
        
        if ($this->budgeted_amount > 0) {
            $this->variance_percentage = round(($this->variance / $this->budgeted_amount) * 100, 2);
        } else {
            $this->variance_percentage = 0;
        }

        $this->updateStatus();
        $this->save();
    }

    /**
     * Update status based on variance
     */
    public function updateStatus(): void
    {
        if ($this->variance_percentage < -10) {
            $this->status = 'exceeded';
        } elseif ($this->variance_percentage < 0) {
            $this->status = 'warning';
        } else {
            $this->status = 'on_track';
        }
    }

    /**
     * Get percentage of budget used
     */
    public function getPercentageUsed(): float
    {
        if ($this->budgeted_amount == 0) {
            return 0;
        }
        return round(($this->actual_amount / $this->budgeted_amount) * 100, 2);
    }

    /**
     * Check if budget is exceeded
     */
    public function isExceeded(): bool
    {
        return $this->actual_amount > $this->budgeted_amount;
    }

    /**
     * Get remaining budget
     */
    public function getRemainingBudget(): float
    {
        return $this->budgeted_amount - $this->actual_amount;
    }
}
