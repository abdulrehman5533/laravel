<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoldSavingsScheme extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 'scheme_name', 'monthly_amount', 'duration_months',
        'start_date', 'end_date', 'accumulated_amount', 'accumulated_weight',
        'status', 'remarks',
    ];

    protected $casts = [
        'start_date'         => 'date',
        'end_date'           => 'date',
        'accumulated_amount' => 'decimal:2',
        'accumulated_weight' => 'decimal:4',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(GoldSavingsPayment::class, 'gold_savings_scheme_id');
    }
}
