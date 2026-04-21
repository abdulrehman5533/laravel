<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'name',
        'first_name',
        'last_name',
        'email',
        'phone',
        'mobile',
        'date_of_birth',
        'gender',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'customer_type', // individual, business
        'company_name',
        'tax_id',
        'credit_limit',
        'opening_balance',
        'opening_gold_balance',
        'opening_fine_gold_balance',
        'opening_silver_balance',
        'opening_fine_silver_balance',
        'current_balance',
        'current_gold_balance',
        'current_fine_gold_balance',
        'current_silver_balance',
        'current_fine_silver_balance',
        'loyalty_points',
        'membership_level', // bronze, silver, gold, platinum
        'preferred_payment_method',
        'notes',
        'is_active',
        'last_purchase_date',
        'total_purchases',
        'average_order_value',
        'referral_source',
        'marketing_consent',
        'special_discount_percentage',
        'branch_id',
        'created_by',
        'id_type',
        'id_number',
        'id_photo_front',
        'id_photo_back',
        'customer_photo',
        'occupation',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'credit_limit' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'opening_gold_balance' => 'decimal:3',
        'opening_fine_gold_balance' => 'decimal:3',
        'opening_silver_balance' => 'decimal:3',
        'opening_fine_silver_balance' => 'decimal:3',
        'current_balance' => 'decimal:2',
        'current_gold_balance' => 'decimal:3',
        'current_fine_gold_balance' => 'decimal:3',
        'current_silver_balance' => 'decimal:3',
        'current_fine_silver_balance' => 'decimal:3',
        'loyalty_points' => 'integer',
        'total_purchases' => 'decimal:2',
        'average_order_value' => 'decimal:2',
        'special_discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'marketing_consent' => 'boolean',
        'last_purchase_date' => 'datetime',
    ];

    // Relationships
    public function sales(): HasMany
    {
        return $this->hasMany(PosSale::class, 'pos_customer_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PosPayment::class, 'pos_customer_id');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(CrmInteraction::class, 'crm_customer_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function metalLedgerEntries(): HasMany
    {
        return $this->hasMany(CustomerMetalLedger::class);
    }

    public function girvis(): HasMany
    {
        return $this->hasMany(Girvi::class);
    }

    public function rateContracts(): HasMany
    {
        return $this->hasMany(CustomerRateContract::class);
    }

    public function getActiveRateContract(?string $metalType = 'gold', ?int $purityId = null)
    {
        return $this->rateContracts()
            ->active()
            ->where('metal_type', $metalType)
            ->where(function ($q) use ($purityId) {
                $q->whereNull('purity_id')->orWhere('purity_id', $purityId);
            })
            ->first();
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($customer) {
            if (empty($customer->name)) {
                $customer->name = trim($customer->first_name.' '.$customer->last_name);
            }
            if (empty($customer->phone) && ! empty($customer->mobile)) {
                $customer->phone = $customer->mobile;
            }
        });
    }

    // Accessors & Mutators
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function getFullAddressAttribute(): string
    {
        $address = $this->address_line_1;
        if ($this->address_line_2) {
            $address .= ', '.$this->address_line_2;
        }
        $address .= ', '.$this->city.', '.$this->state.' '.$this->postal_code;
        if ($this->country) {
            $address .= ', '.$this->country;
        }

        return $address;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('customer_type', $type);
    }

    public function scopeWithCreditLimit($query)
    {
        return $query->where('credit_limit', '>', 0);
    }

    // Helper methods
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

    public function hasAvailableCredit($amount): bool
    {
        if ($this->credit_limit <= 0) {
            return false;
        }

        return ($this->current_balance + $amount) <= $this->credit_limit;
    }

    /**
     * Enterprise Exposure Tracking: Balance + Aging check
     */
    public function getCreditExposureReport(): array
    {
        $overdueBalance = $this->sales()
            ->where('status', 'completed')
            ->where('due_date', '<', now())
            ->where('outstanding_balance', '>', 0)
            ->sum('outstanding_balance');

        $totalExposure = $this->current_balance;
        $utilization = ($this->credit_limit > 0) ? ($totalExposure / $this->credit_limit) * 100 : 0;

        return [
            'total_exposure' => (float) $totalExposure,
            'overdue_balance' => (float) $overdueBalance,
            'credit_limit' => (float) $this->credit_limit,
            'available_credit' => (float) ($this->credit_limit - $totalExposure),
            'utilization_percent' => round($utilization, 2),
            'is_over_limit' => $totalExposure > $this->credit_limit,
            'has_overdue' => $overdueBalance > 0,
        ];
    }

    public function addLoyaltyPoints($points): void
    {
        $this->increment('loyalty_points', $points);
    }

    public function deductLoyaltyPoints($points): bool
    {
        if ($this->loyalty_points >= $points) {
            $this->decrement('loyalty_points', $points);

            return true;
        }

        return false;
    }

    public function updatePurchaseStats($purchaseAmount): void
    {
        $this->last_purchase_date = now();
        $this->total_purchases += $purchaseAmount;

        // Recalculate average order value
        $totalOrders = $this->sales()->count();
        if ($totalOrders > 0) {
            $this->average_order_value = $this->total_purchases / $totalOrders;
        }

        $this->save();
    }
}
