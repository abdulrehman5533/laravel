<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StoneAttribute extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'product_id', 'stone_type', 'shape', 'cut', 'clarity', 'color', 'fluorescence', 'carat',
        'price_per_carat', 'total_stone_value', 'origin', 'treatment', 'certification', 'lab', 'certificate_number',
    ];

    protected $casts = [
        'carat' => 'decimal:4',
        'price_per_carat' => 'decimal:2',
        'total_stone_value' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saving(function ($stone) {
            if ($stone->carat && $stone->price_per_carat) {
                $stone->total_stone_value = $stone->carat * $stone->price_per_carat;
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'product_id');
    }
}
