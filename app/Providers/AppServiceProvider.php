<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\GoldRate::observe(\App\Observers\GoldRateObserver::class);

        // Define application gates
        Gate::define('manage-accounts', function ($user) {
            if (! $user) {
                return false;
            }
            return ($user->is_super_admin) || (method_exists($user, 'hasRole') && $user->hasRole('accounts_manager'));
        });

        // Use the user's hasPermission method for all @can checks
        Gate::before(function ($user, $ability) {
            $data = [
                'time' => date('Y-m-d H:i:s'),
                'user_id' => $user->id,
                'email' => $user->email,
                'is_super_admin' => $user->is_super_admin,
                'role_slug' => $user->role?->slug,
                'ability' => $ability
            ];

            if ($user->is_super_admin || ($user->relationLoaded('role') && $user->role?->slug === 'admin') || $user->role?->slug === 'admin') {
                return true;
            }
            
            if (method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability) ?: null;
            }
        });

        // Field Level Permissions
        Blade::if('fieldRead', function ($model, $field) {
            if (! Auth::check()) {
                return false;
            }
            $user = Auth::user();
            if (method_exists($user, 'getFieldPermission')) {
                return $user->getFieldPermission($model, $field) !== 'none';
            }
            return false;
        });

        Blade::if('fieldWrite', function ($model, $field) {
            if (! Auth::check()) {
                return false;
            }
            $user = Auth::user();
            if (method_exists($user, 'getFieldPermission')) {
                return $user->getFieldPermission($model, $field) === 'write';
            }
            return false;
        });
    }
}
