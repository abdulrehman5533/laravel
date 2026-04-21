<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('security_manager.view') || $user->role?->slug === 'admin';
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermission('security_manager.view') || $user->role?->slug === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('security_manager.create') || $user->role?->slug === 'admin';
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermission('security_manager.update') || $user->role?->slug === 'admin';
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermission('security_manager.delete') || $user->role?->slug === 'admin';
    }
}
