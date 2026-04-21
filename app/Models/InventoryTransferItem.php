<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryTransferItem extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = ['transfer_id', 'product_id', 'quantity', 'received_quantity'];

    protected $casts = [
        'quantity' => 'decimal:4',
        'received_quantity' => 'decimal:4',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InventoryTransfer::class, 'transfer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }
}
