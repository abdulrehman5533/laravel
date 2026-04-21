<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyPointsLedger extends Model
{
    protected $table = 'loyalty_points_ledger';

    protected $fillable = [
        'customer_id',
        'points',
        'transaction_type',
        'source_type',
        'source_id',
        'description',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
