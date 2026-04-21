<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductBomItem extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = [
        'product_bom_id', 'type', 'item_name', 'item_description',
        'quantity', 'unit', 'weight', 'stone_type', 'stone_shape',
        'stone_color', 'stone_clarity', 'stone_cut', 'estimated_cost',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'weight' => 'decimal:4',
        'estimated_cost' => 'decimal:2',
    ];

    public function bom(): BelongsTo
    {
        return $this->belongsTo(ProductBom::class, 'product_bom_id');
    }
}
