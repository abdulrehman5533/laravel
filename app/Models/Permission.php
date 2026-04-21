<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = ['tenant_id', 'name', 'slug', 'description', 'resource', 'action'];

    /**
     * Get the roles that have this permission.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * Create permission from resource and action.
     */
    public static function createFromAction($resource, $action, $description = null): static
    {
        return static::firstOrCreate(
            ['slug' => "$resource.$action"],
            [
                'name' => "$action ".str(ucfirst($resource))->title(),
                'description' => $description,
                'resource' => $resource,
                'action' => $action,
            ]
        );
    }
}
