<?php

namespace App\Policies;

use App\Models\SecurityAuditLog;
use App\Models\User;

class SecurityAuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('security_manager.view') || $user->role?->slug === 'admin';
    }

    public function view(User $user, SecurityAuditLog $log): bool
    {
        return $user->hasPermission('security_manager.view') || $user->role?->slug === 'admin';
    }
}
