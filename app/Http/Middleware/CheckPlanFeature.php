<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPlanFeature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();
        
        // Super Admin bypasses plan feature checks
        if ($user && ($user->is_super_admin || $user->role?->slug === 'admin')) {
            return $next($request);
        }

        $tenant = app('current_tenant');

        if (! $tenant || ! $tenant->hasFeature($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Your current plan does not include the '{$feature}' feature. Please upgrade your subscription.",
                ], 403);
            }

            return redirect()->route('subscription.index')
                ->with('error', "Your current plan does not include the '{$feature}' feature. Please upgrade your subscription.");
        }

        return $next($request);
    }
}
