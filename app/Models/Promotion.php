<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Promotion extends Model
{
    protected $fillable = [
        'name',
        'description',
        'type',
        'value',
        'start_date',
        'end_date',
        'is_active',
        'rules',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'rules' => 'json',
        'value' => 'decimal:2',
    ];

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }
}
