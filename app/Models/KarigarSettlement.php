<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KarigarSettlement extends Model
{
    use BelongsToTenant, HasAuditLog;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'branch_id', 'supplier_id', 'settlement_number', 'date', 'metal_type',
        'fine_weight_fixed', 'fixed_rate', 'metal_value', 'labor_amount',
        'other_charges', 'total_amount', 'payment_status', 'notes', 'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'fine_weight_fixed' => 'decimal:3',
        'fixed_rate' => 'decimal:2',
        'metal_value' => 'decimal:2',
        'labor_amount' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function karigar(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
