<?php

namespace App\Models;

use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes, HasAuditLog;

    protected $fillable = [
        'name',
        'domain',
        'subdomain',
        'database_name', // for future flexibility
        'is_active',
        'plan_id',
        'trial_ends_at',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'settings' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Check if the tenant has access to a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        if (! $this->plan) {
            return false;
        }

        $features = $this->plan->features ?? [];

        if (in_array('All Features', $features)) {
            return true;
        }

        return in_array($feature, $features);
    }

    /**
     * Check if the tenant has reached a specific limit.
     */
    public function reachedLimit(string $type): bool
    {
        if (! $this->plan) {
            return true;
        }

        $limit = $this->plan->{"max_{$type}"};

        // -1 means unlimited
        if ($limit === -1) {
            return false;
        }

        $currentUsage = $this->getCurrentUsage($type);

        return $currentUsage >= $limit;
    }

    /**
     * Get the current usage count for a specific type.
     */
    public function getCurrentUsage(string $type): int
    {
        return match ($type) {
            'users' => $this->users()->count(),
            'branches' => $this->branches()->count(),
            'products' => \App\Models\InventoryProduct::where('tenant_id', $this->id)->count(),
            default => 0,
        };
    }
}
