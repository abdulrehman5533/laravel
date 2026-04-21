<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryProductIntelligence extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $table = 'inventory_product_intelligence';

    protected $fillable = [
        'product_id', 'risk_score', 'movement_speed', 'last_sold_at',
        'ai_suggestions', 'last_analyzed_at',
    ];

    protected $casts = [
        'risk_score' => 'decimal:2',
        'last_sold_at' => 'date',
        'ai_suggestions' => 'array',
        'last_analyzed_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }
}
