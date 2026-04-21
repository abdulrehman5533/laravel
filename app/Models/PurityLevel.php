<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurityLevel extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'name', 'karat', 'percentage', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'percentage' => 'decimal:4',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(InventoryProduct::class, 'purity_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
