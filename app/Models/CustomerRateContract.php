<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerRateContract extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = [
        'customer_id', 'metal_type', 'purity_id', 'contract_type',
        'value', 'start_date', 'end_date', 'is_active', 'reference_number',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function purity(): BelongsTo
    {
        return $this->belongsTo(PurityLevel::class, 'purity_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('start_date', '<=', today())
            ->where(function ($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', today());
            });
    }

    public function applyToRate(float $liveRate): float
    {
        return match ($this->contract_type) {
            'fixed' => (float) $this->value,
            'discount_from_live' => $liveRate - $this->value,
            'premium_on_live' => $liveRate + $this->value,
            default => $liveRate,
        };
    }
}
