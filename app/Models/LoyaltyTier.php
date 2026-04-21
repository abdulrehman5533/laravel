<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyTier extends Model
{
    protected $fillable = ['name', 'min_points', 'multiplier', 'benefits'];

    protected $casts = [
        'benefits' => 'json',
        'multiplier' => 'decimal:2',
    ];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
