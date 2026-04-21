<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GirviTopup extends Model
{
    protected $fillable = [
        'girvi_id', 'topup_date', 'topup_amount', 'new_interest_rate',
        'new_maturity_date', 'additional_items', 'approved_by', 'notes'
    ];

    protected $casts = [
        'topup_date' => 'date',
        'new_maturity_date' => 'date',
        'topup_amount' => 'decimal:2',
        'new_interest_rate' => 'decimal:2',
        'additional_items' => 'array',
    ];

    public function girvi(): BelongsTo
    {
        return $this->belongsTo(Girvi::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
