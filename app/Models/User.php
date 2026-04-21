<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use BelongsToTenant, HasAuditLog, SoftDeletes;
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'is_super_admin',
        'avatar',
        'password',
        'phone',
        'user_type',
        'is_active',
        'role_id',
        'branch_id',
        'user_status',
        'last_login_at',
        'password_changed_at',
        'force_password_change',
        'failed_login_attempts',
        'locked_until',
        'session_timeout_minutes',
        'idle_timeout_minutes',
        'minimize_timeout_minutes',
        'background_timeout_minutes',
        'enable_idle_logout',
        'enable_minimize_logout',
        'enable_background_logout',
        'allowed_login_start',
        'allowed_login_end',
        'allowed_days',
        'authorized_until',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'password_changed_at' => 'datetime',
        'force_password_change' => 'boolean',
        'locked_until' => 'datetime',
        'session_timeout_minutes' => 'integer',
        'idle_timeout_minutes' => 'integer',
        'minimize_timeout_minutes' => 'integer',
        'background_timeout_minutes' => 'integer',
        'enable_idle_logout' => 'boolean',
        'enable_minimize_logout' => 'boolean',
        'enable_background_logout' => 'boolean',
        'allowed_days' => 'array',
        'authorized_until' => 'date',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function posSales()
    {
        return $this->hasMany(PosSale::class, 'created_by');
    }

    public function sessions()
    {
        return $this->hasMany(UserSession::class);
    }

    public function activeSessions()
    {
        return $this->sessions()->active();
    }

    public function auditLogs()
    {
        return $this->hasMany(SecurityAuditLog::class);
    }

    public function hasPermission($permission): bool
    {
        if ($this->is_super_admin) {
            return true;
        }
        if (! $this->role) {
            return false;
        }
        if ($this->role->slug === 'admin') {
            return true;
        }

        return $this->role->hasPermission($permission);
    }

    public function hasAnyPermission($permissions): bool
    {
        if ($this->is_super_admin) {
            return true;
        }
        if (! $this->role) {
            return false;
        }
        if ($this->role->slug === 'admin') {
            return true;
        }
        foreach ($permissions as $permission) {
            if ($this->role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasAllPermissions($permissions): bool
    {
        if ($this->is_super_admin) {
            return true;
        }
        if (! $this->role) {
            return false;
        }
        if ($this->role->slug === 'admin') {
            return true;
        }
        foreach ($permissions as $permission) {
            if (! $this->role->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    public function hasRole($role): bool
    {
        if ($this->is_super_admin) {
            return true;
        }
        if (is_string($role)) {
            return $this->role?->slug === $role || $this->role?->name === $role;
        }

        return $this->role_id === $role->id;
    }

    public function isLocked(): bool
    {
        if ($this->user_status === 'locked') {
            return true;
        }

        if ($this->locked_until && $this->locked_until->isFuture()) {
            return true;
        }

        return false;
    }

    public function isSuspended(): bool
    {
        return $this->user_status === 'suspended';
    }

    /**
     * Check if user is within authorized access period.
     */
    public function isWithinAuthorizedPeriod(): bool
    {
        if ($this->authorized_until && $this->authorized_until->isPast()) {
            return false;
        }

        $now = now();

        // Check allowed days
        if ($this->allowed_days && ! empty($this->allowed_days)) {
            $currentDay = $now->format('l');
            if (! in_array($currentDay, $this->allowed_days)) {
                return false;
            }
        }

        // Check allowed time
        if ($this->allowed_login_start && $this->allowed_login_end) {
            $currentTime = $now->format('H:i:s');
            if ($currentTime < $this->allowed_login_start || $currentTime > $this->allowed_login_end) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get effective session timeout in minutes.
     */
    public function getSessionTimeout(): int
    {
        return $this->session_timeout_minutes ?? $this->role?->session_timeout_minutes ?? config('session.lifetime');
    }

    /**
     * Check field permission for a model.
     */
    public function getFieldPermission(string $model, string $field): string
    {
        if ($this->role?->slug === 'admin') {
            return 'write';
        }

        $permission = FieldPermission::where('role_id', $this->role_id)
            ->where('model_name', $model)
            ->where('field_name', $field)
            ->first();

        return $permission?->permission ?? 'write'; // Default to write if not explicitly restricted?
        // Actually for security, maybe default to read or none?
        // But for backward compatibility with existing features, write is safer.
    }

    /**
     * Get the avatar URL.
     */
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && file_exists(public_path($this->avatar))) {
            return asset($this->avatar);
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&color=7F9CF5&background=EBF4FF';
    }

    /**
     * Get the dashboard route name for the user's role.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function getDashboardRoute(): string
    {
        if (! $this->role) {
            return 'dashboard';
        }

        return match ($this->role->slug) {
            'super_admin' => 'central.dashboard',
            'admin' => 'dashboard',
            'account_manager', 'accounts_manager' => 'manager.dashboard',
            'accountant' => 'accounts.accounting-dashboard.index',
            'cashier' => 'pos.index',
            'viewer' => 'viewer.dashboard',
            'security_manager' => 'security-manager.dashboard',
            'inventory_manager' => 'inventory.dashboard',
            'hr_manager' => 'hr.dashboard',
            default => 'dashboard',
        };
    }
}
