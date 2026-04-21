<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSession extends Model
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'device_type',
        'ip_address',
        'user_agent',
        'login_at',
        'last_activity_at',
        'terminated_at',
        'termination_reason',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'terminated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->terminated_at === null;
    }

    public function terminate($reason = null): static
    {
        $this->update([
            'terminated_at' => now(),
            'termination_reason' => $reason,
        ]);

        return $this;
    }

    public function scopeActive($query)
    {
        return $query->whereNull('terminated_at');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRecent($query, $minutes = 120)
    {
        return $query->where('last_activity_at', '>=', now()->subMinutes($minutes));
    }
}
