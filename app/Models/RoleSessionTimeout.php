<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleSessionTimeout extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;

    protected $fillable = ['role_id', 'timeout_minutes', 'is_default'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public static function getTimeoutForRole($roleId): int
    {
        $timeout = static::where('role_id', $roleId)->first();

        return $timeout?->timeout_minutes ?? config('session.lifetime', 120);
    }
}
