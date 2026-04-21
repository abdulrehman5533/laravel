<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'key',
        'secret',
        'status',
        'expires_at',
        'ip_whitelist',
        'rate_limit',
        'scopes',
        'last_used_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
        'ip_whitelist' => 'json',
        'scopes' => 'json',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function usageLogs()
    {
        return $this->hasMany(ApiUsageLog::class);
    }
}
