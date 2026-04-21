<?php

/**
 * CustomerMetalLedger.php
 * Updated: 2026-01-09
 */

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerMetalLedger extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'branch_id',
        'transaction_date',
        'transaction_type',
        'metal_type',
        'purity_percentage',
        'weight_in',
        'fine_weight_in',
        'weight_out',
        'fine_weight_out',
        'running_weight_balance',
        'running_fine_balance',
        'reference_type',
        'reference_id',
        'rate_at_transaction',
        'conversion_amount',
        'description',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'weight_in' => 'decimal:3',
        'fine_weight_in' => 'decimal:3',
        'weight_out' => 'decimal:3',
        'fine_weight_out' => 'decimal:3',
        'running_weight_balance' => 'decimal:3',
        'running_fine_balance' => 'decimal:3',
        'purity_percentage' => 'decimal:4',
        'rate_at_transaction' => 'decimal:2',
        'conversion_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
