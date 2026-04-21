<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'is_active',
        'session_timeout_minutes',
        'idle_timeout_minutes',
        'minimize_timeout_minutes',
        'background_timeout_minutes',
        'enable_idle_logout',
        'enable_minimize_logout',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'session_timeout_minutes' => 'integer',
        'idle_timeout_minutes' => 'integer',
        'minimize_timeout_minutes' => 'integer',
        'background_timeout_minutes' => 'integer',
        'enable_idle_logout' => 'boolean',
        'enable_minimize_logout' => 'boolean',
    ];

    /**
     * Get the permissions associated with this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Get the users with this role.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get field permissions for this role.
     */
    public function fieldPermissions()
    {
        return $this->hasMany(FieldPermission::class);
    }

    /**
     * Check if role has permission.
     */
    public function hasPermission($permission): bool
    {
        return $this->permissions()
            ->where('slug', $permission)
            ->orWhere('name', $permission)
            ->exists();
    }

    /**
     * Give permission to role.
     */
    public function givePermission($permission): static
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)
                ->orWhere('name', $permission)
                ->firstOrFail();
        }

        $this->permissions()->syncWithoutDetaching($permission);

        return $this;
    }

    /**
     * Remove permission from role.
     */
    public function revokePermission($permission): static
    {
        if (is_string($permission)) {
            $permission = Permission::where('slug', $permission)
                ->orWhere('name', $permission)
                ->firstOrFail();
        }

        $this->permissions()->detach($permission);

        return $this;
    }
}
