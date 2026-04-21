<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CleanupStaleSessions
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $currentSessionId = $request->session()->getId();
            $sessionLifetime = config('session.lifetime', 120); // in minutes
            $staleThreshold = time() - ($sessionLifetime * 60);

            // Clean up stale sessions for this user
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $currentSessionId)
                ->where('last_activity', '<', $staleThreshold)
                ->delete();

            // Check if current session is valid
            $currentSession = DB::table('sessions')
                ->where('id', $currentSessionId)
                ->where('user_id', $user->id)
                ->first();

            if (!$currentSession) {
                // Current session doesn't exist in DB - force logout
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('status', 'Your session has expired. Please login again.');
            }
        }

        return $next($request);
    }
}
