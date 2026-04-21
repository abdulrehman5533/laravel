<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockAlert extends Model
{
    use BelongsToTenant, SoftDeletes;
    use HasAuditLog;

    protected $table = 'inventory_stock_alerts';

    protected $fillable = [
        'product_id', 'severity', 'message', 'is_acknowledged',
        'snoozed_until', 'acknowledged_by', 'acknowledged_at',
    ];

    protected $casts = [
        'is_acknowledged' => 'boolean',
        'snoozed_until' => 'datetime',
        'acknowledged_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }
}
