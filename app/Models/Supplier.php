<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BelongsToTenant, HasAuditLog;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'name', 'company_name', 'contact_person', 'email', 'phone_primary',
        'phone_secondary', 'address', 'city', 'state', 'postal_code',
        'country', 'gstin', 'ntn', 'pan', 'bank_account_number', 'bank_name',
        'ifsc_code', 'payment_terms', 'payment_days', 'supplier_type',
        'product_specialty', 'rating', 'quality_score', 'delivery_score',
        'status', 'gst_registered', 'credit_limit', 'current_credit_used',
        'opening_balance', 'opening_balance_date', 'total_purchases',
        'current_gold_balance', 'current_fine_gold_balance', 'current_silver_balance',
        'current_fine_silver_balance', 'opening_gold_balance', 'opening_fine_gold_balance',
        'total_purchased_amount', 'total_paid_amount', 'notes', 'registration_number',
        'registration_date', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'opening_balance_date' => 'date',
        'gst_registered' => 'boolean',
        'rating' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'current_gold_balance' => 'decimal:3',
        'current_fine_gold_balance' => 'decimal:3',
        'current_silver_balance' => 'decimal:3',
        'current_fine_silver_balance' => 'decimal:3',
        'opening_gold_balance' => 'decimal:3',
        'opening_fine_gold_balance' => 'decimal:3',
        'total_purchases' => 'decimal:2',
        'total_purchased_amount' => 'decimal:2',
        'total_paid_amount' => 'decimal:2',
    ];

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function purchaseReturns(): HasMany
    {
        return $this->hasMany(PurchaseReturn::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function priceHistories(): HasMany
    {
        return $this->hasMany(PurchasePriceHistory::class);
    }

    public function creditLimits(): HasMany
    {
        return $this->hasMany(SupplierCreditLimit::class);
    }

    public function ledger(): HasMany
    {
        return $this->hasMany(SupplierLedger::class);
    }

    /**
     * Get or create ledger for a specific branch
     */
    public function getLedgerForBranch($branchId): SupplierLedger
    {
        return $this->ledger()->firstOrCreate(
            ['branch_id' => $branchId],
            [
                'opening_balance' => 0,
                'current_balance' => 0,
                'status' => 'active',
            ]
        );
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(SupplierLedgerEntry::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(PurchaseAnalytic::class);
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

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('supplier_type', $type);
    }

    public function scopeWithOverduePayments($query)
    {
        return $query->where('has_overdue', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('phone_primary', 'like', "%{$term}%");
    }

    public function getMetalBalancesAttribute(): array
    {
        return [
            'gold' => [
                'gross' => (float) $this->current_gold_balance,
                'fine' => (float) $this->current_fine_gold_balance,
            ],
            'silver' => [
                'gross' => (float) $this->current_silver_balance,
                'fine' => (float) $this->current_fine_silver_balance,
            ],
        ];
    }

    public function getFormattedMetalBalancesAttribute(): string
    {
        $parts = [];
        if ($this->current_fine_gold_balance != 0) {
            $parts[] = 'Gold: '.number_format($this->current_fine_gold_balance, 3).'g';
        }
        if ($this->current_fine_silver_balance != 0) {
            $parts[] = 'Silver: '.number_format($this->current_fine_silver_balance, 3).'g';
        }

        return empty($parts) ? 'No metal balance' : implode(', ', $parts);
    }

    // Accessors
    public function getOutstandingBalanceAttribute()
    {
        $purchaseBalance = $this->purchaseOrders()
            ->where('payment_status', '!=', 'Paid')
            ->sum('amount_due');

        $openingBalance = $this->opening_balance ?? 0;

        return $openingBalance + $purchaseBalance;
    }

    public function getBalanceDetailsAttribute()
    {
        return [
            'opening_balance' => $this->opening_balance ?? 0,
            'total_purchases' => $this->purchaseOrders()->where('status', '!=', 'Cancelled')->sum('total_amount'),
            'total_paid' => $this->payments()->sum('amount_paid'),
            'total_returns' => $this->purchaseReturns()->where('status', 'Processed')->sum('refund_amount'),
        ];
    }

    public function getAvailableCreditAttribute()
    {
        $creditLimit = $this->creditLimits()->where('status', 'Active')->first();
        if (! $creditLimit) {
            return 0;
        }

        return $creditLimit->approved_credit_limit - $creditLimit->current_credit_used;
    }

    public function getIsBlockedAttribute()
    {
        return $this->status === 'Blocked' ||
               ($this->creditLimits()->where('status', 'Blocked')->exists());
    }

    public function getAverageRatingAttribute()
    {
        return (($this->quality_score / 100) + ($this->delivery_score / 100)) / 2;
    }
}
