<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vault extends Model
{
    use BelongsToTenant, HasAuditLog, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id', 'branch_id', 'name', 'code', 'type',
        'security_level', 'capacity_items', 'is_active', 'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GirviItem::class);
    }

    /**
     * Enterprise: Check if vault has capacity
     */
    public function hasCapacity(): bool
    {
        if ($this->capacity_items <= 0) {
            return true;
        } // Unlimited if 0

        return $this->items()->where('status', 'in_vault')->count() < $this->capacity_items;
    }
}
