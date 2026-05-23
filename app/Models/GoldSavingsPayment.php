<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoldSavingsPayment extends Model
{
    protected $fillable = [
        'gold_savings_scheme_id', 'amount', 'gold_rate',
        'gold_weight', 'payment_date', 'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount'       => 'decimal:2',
        'gold_rate'    => 'decimal:2',
        'gold_weight'  => 'decimal:4',
    ];

    public function scheme()
    {
        return $this->belongsTo(GoldSavingsScheme::class, 'gold_savings_scheme_id');
    }
}
