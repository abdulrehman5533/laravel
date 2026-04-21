<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransfer extends Model
{
    use BelongsToTenant, SoftDeletes;
    use HasAuditLog;

    protected $fillable = [
        'transfer_number', 'from_branch_id', 'to_branch_id', 'status', 'otp_code', 'is_otp_verified',
        'discrepancy_found', 'discrepancy_notes', 'waybill_number', 'security_seal_number', 'notes',
        'requested_by', 'approved_by', 'dispatched_by', 'received_by',
        'dispatched_at', 'received_at',
    ];

    protected $casts = [
        'is_otp_verified' => 'boolean',
        'discrepancy_found' => 'boolean',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryTransferItem::class, 'transfer_id');
    }
}
