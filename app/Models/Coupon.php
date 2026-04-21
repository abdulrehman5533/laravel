<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $fillable = [
        'code',
        'promotion_id',
        'usage_limit',
        'used_count',
        'min_purchase_amount',
    ];

    protected $casts = [
        'min_purchase_amount' => 'decimal:2',
    ];

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}
