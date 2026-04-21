<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $table = 'hr_assets';

    protected $fillable = [
        'name',
        'asset_tag',
        'category',
        'serial_number',
        'value',
        'purchase_date',
        'status',
        'description',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'value' => 'decimal:2',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasMany(AssetAssignment::class)->whereNull('returned_at')->first();
    }
}
