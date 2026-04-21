<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CentralAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For SaaS, we usually have a special flag on users or a specific role for global admin.
        // For now, we will check if the user is authenticated and has a special email or flag.
        // In a real app, this would be a 'is_super_admin' column in the users table.

        if (! auth()->check() || ! auth()->user()->is_super_admin) {
            abort(403, 'Unauthorized access to central administration.');
        }

        return $next($request);
    }
}
