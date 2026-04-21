<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Girvi extends Model
{
    use BelongsToTenant, HasAuditLog;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'girvi_number', 'customer_id', 'branch_id', 'loan_amount',
        'interest_rate', 'interest_cycle', 'interest_type',
        'locked_gold_rate', 'locked_silver_rate', 'girvi_date',
        'maturity_date', 'status', 'principal_paid', 'interest_accrued',
        'interest_paid', 'penalty_amount', 'outstanding_amount',
        'transfer_type', 'transferred_to', 'transfer_amount', 'transfer_date',
        'kyc_verified', 'risk_score', 'ltv_ratio', 'internal_notes', 'created_by',
        'grace_period_days', 'penal_interest_rate', 'rounding_rule',
        'rate_source', 'risk_alert_threshold', 'is_high_risk',
        'transfer_charges', 'transfer_terms',
        'approval_status', 'approved_by', 'approved_at',
        'loan_purpose', 'guarantor_name', 'guarantor_phone',
        'guarantor_id_type', 'guarantor_id_number',
        'auto_renew', 'renewal_count', 'last_renewed_at',
    ];

    protected $casts = [
        'girvi_date' => 'date',
        'maturity_date' => 'date',
        'transfer_date' => 'date',
        'kyc_verified' => 'boolean',
        'loan_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'penal_interest_rate' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'transfer_amount' => 'decimal:2',
        'transfer_charges' => 'decimal:2',
        'ltv_ratio' => 'decimal:2',
        'is_high_risk' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GirviItem::class);
    }

    public function interestPostings(): HasMany
    {
        return $this->hasMany(GirviInterestPosting::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(GirviPayment::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(GirviReminder::class);
    }

    public function auctions(): HasMany
    {
        return $this->hasMany(GirviAuction::class);
    }

    public function partialReleases(): HasMany
    {
        return $this->hasMany(GirviPartialRelease::class);
    }

    public function topups(): HasMany
    {
        return $this->hasMany(GirviTopup::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Enterprise Risk: Calculate Current Valuation of all items
     */
    public function calculateTotalValuation(): float
    {
        return (float) $this->items->sum('estimated_value');
    }

    /**
     * Enterprise Risk: Update LTV Ratio based on current valuation
     */
    public function updateLtvRatio(): void
    {
        $valuation = $this->calculateTotalValuation();
        if ($valuation > 0) {
            $this->ltv_ratio = ($this->outstanding_amount / $valuation) * 100;

            // Flag high risk if LTV exceeds threshold (e.g. 85%)
            if ($this->ltv_ratio > ($this->risk_alert_threshold ?? 85)) {
                $this->is_high_risk = true;
            }

            $this->save();
        }
    }

    /**
     * Enterprise: NPA (Non-Performing Asset) Classification
     */
    public function getNpaStatus(): string
    {
        $daysOverdue = $this->maturity_date->diffInDays(now(), false);

        if ($daysOverdue <= 0) {
            return 'Standard';
        }
        if ($daysOverdue <= 90) {
            return 'Sub-Standard';
        }
        if ($daysOverdue <= 180) {
            return 'Doubtful';
        }

        return 'Loss / NPA';
    }
}
