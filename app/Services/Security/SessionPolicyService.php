<?php

namespace App\Services\Security;

use App\Models\SystemSetting;
use App\Models\User;

class SessionPolicyService
{
    /**
     * Get the effective session timeout for a user.
     */
    public function getSessionTimeout(User $user): int
    {
        return $user->session_timeout_minutes
            ?? $user->role?->session_timeout_minutes
            ?? SystemSetting::get('default_session_timeout', config('session.lifetime'));
    }

    /**
     * Get the effective idle timeout for a user.
     */
    public function getIdleTimeout(User $user): int
    {
        if (! $this->isIdleLogoutEnabled($user)) {
            return 0;
        }

        return $user->idle_timeout_minutes
            ?? $user->role?->idle_timeout_minutes
            ?? SystemSetting::get('default_idle_timeout', 15);
    }

    /**
     * Get the effective minimize timeout for a user.
     */
    public function getMinimizeTimeout(User $user): int
    {
        if (! $this->isMinimizeLogoutEnabled($user)) {
            return 0;
        }

        return $user->minimize_timeout_minutes
            ?? $user->role?->minimize_timeout_minutes
            ?? SystemSetting::get('default_minimize_timeout', 30);
    }

    /**
     * Get the effective background timeout for a user.
     */
    public function getBackgroundTimeout(User $user): int
    {
        if (! $this->isBackgroundLogoutEnabled($user)) {
            return 0;
        }

        return $user->background_timeout_minutes
            ?? $user->role?->background_timeout_minutes
            ?? SystemSetting::get('default_background_timeout', 15);
    }

    /**
     * Check if idle logout is enabled for a user.
     */
    public function isIdleLogoutEnabled(User $user): bool
    {
        return $user->enable_idle_logout
            ?? $user->role?->enable_idle_logout
            ?? SystemSetting::get('enable_idle_logout', true);
    }

    /**
     * Check if minimize logout is enabled for a user.
     */
    public function isMinimizeLogoutEnabled(User $user): bool
    {
        return $user->enable_minimize_logout
            ?? $user->role?->enable_minimize_logout
            ?? SystemSetting::get('enable_minimize_logout', false);
    }

    /**
     * Check if background logout is enabled for a user.
     */
    public function isBackgroundLogoutEnabled(User $user): bool
    {
        return $user->enable_background_logout
            ?? $user->role?->enable_background_logout
            ?? SystemSetting::get('enable_background_logout', true);
    }

    /**
     * Get global session settings.
     */
    public function getGlobalSettings(): array
    {
        return [
            'default_session_timeout' => SystemSetting::get('default_session_timeout', 120),
            'default_idle_timeout' => SystemSetting::get('default_idle_timeout', 15),
            'default_minimize_timeout' => SystemSetting::get('default_minimize_timeout', 30),
            'default_background_timeout' => SystemSetting::get('default_background_timeout', 15),
            'enable_idle_logout' => SystemSetting::get('enable_idle_logout', true),
            'enable_minimize_logout' => SystemSetting::get('enable_minimize_logout', false),
            'popup_warning_minutes' => SystemSetting::get('popup_warning_minutes', 5),
            'auto_extend_session' => SystemSetting::get('auto_extend_session', true),
        ];
    }
}
