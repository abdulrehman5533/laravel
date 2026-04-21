<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleDashboardAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Ensure role relationship is loaded and fresh
            if (!$user->relationLoaded('role')) {
                $user->load('role');
            }

            $currentRoute = $request->route() ? $request->route()->getName() : null;
            $dashboardRoute = $user->getDashboardRoute();

            // List of all dashboard routes that should be role-restricted
            $allDashboards = [
                'dashboard',
                'manager.dashboard',
                'accounts.accounting-dashboard.index',
                'accounting-dashboard.index',
                'pos.index',
                'viewer.dashboard',
                'security-manager.dashboard',
                'hr.dashboard',
                'inventory.dashboard',
            ];

            // If user is trying to access a restricted dashboard route that is NOT their assigned one
            if ($currentRoute && in_array($currentRoute, $allDashboards)) {
                // Check if this is the assigned dashboard for the user
                if ($currentRoute !== $dashboardRoute) {
                    // Admins can access everything, but we redirect them to 'dashboard' if they hit others
                    if ($user->is_super_admin || $user->role?->slug === 'admin') {
                        return $next($request);
                    }

                    // Avoid redirecting if the route name is just a variation (fallback check)
                    if ($currentRoute === 'accounting-dashboard.index' && $dashboardRoute === 'accounts.accounting-dashboard.index') {
                        return $next($request);
                    }

                    // Log the unauthorized dashboard access attempt
                    \Illuminate\Support\Facades\Log::warning("Unauthorized dashboard access attempt by user {$user->id} ({$user->role?->slug}) from {$currentRoute} to {$dashboardRoute}");
                    
                    // Check if the route exists before redirecting to avoid 500
                    if (\Illuminate\Support\Facades\Route::has($dashboardRoute)) {
                        return redirect()->route($dashboardRoute)->with('error', 'You have been redirected to your authorized dashboard.');
                    } else {
                        \Illuminate\Support\Facades\Log::error("Dashboard route '{$dashboardRoute}' not found for role '{$user->role?->slug}'");
                        return redirect()->route('dashboard')->with('error', 'Dashboard for your role is not configured. Redirected to main dashboard.');
                    }
                }
            }
        }

        return $next($request);
    }
}
