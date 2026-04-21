<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401, 'Unauthorized');
        }

        $data = [
            'time' => date('Y-m-d H:i:s'),
            'type' => 'middleware',
            'user_id' => $user->id,
            'is_super_admin' => $user->is_super_admin,
            'role_slug' => $user->role?->slug,
            'permissions_requested' => $permissions
        ];
        file_put_contents(storage_path('gate_debug.log'), json_encode($data) . PHP_EOL, FILE_APPEND);

        // Ensure role relationship is loaded and fresh
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        // Admin/Super Admin bypasses all permission checks
        if ($user->is_super_admin || $user->role?->slug === 'admin') {
            return $next($request);
        }

        // If user has no role, they definitely have no permissions
        if (!$user->role) {
            \Illuminate\Support\Facades\Log::error("User {$user->id} has no assigned role.");
            abort(403, 'Your account has no assigned role. Please contact an administrator.');
        }

        $userPermissions = $user->role->permissions()
            ->pluck('slug')
            ->toArray() ?? [];

        $hasPermission = false;
        foreach ($permissions as $permission) {
            if (in_array($permission, $userPermissions)) {
                $hasPermission = true;
                break;
            }
        }

        if (! $hasPermission) {
            \Illuminate\Support\Facades\Log::warning("Permission denied for user {$user->id} ({$user->role->slug}) for permissions: " . implode(', ', $permissions));
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
