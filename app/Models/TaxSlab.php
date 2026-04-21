<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxSlab extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'tax_type',
        'rate',
        'country',
        'hsn_code',
        'is_default',
        'is_active',
        'description',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Scope for active tax slabs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get default tax slab
     */
    public static function getDefault()
    {
        return self::where('is_default', true)->where('is_active', true)->first();
    }
}
