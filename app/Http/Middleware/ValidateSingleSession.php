<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateSingleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            $currentSessionId = session()->getId();
            
            // If user has no current_session_id, set it
            if (!$user->current_session_id) {
                $user->current_session_id = $currentSessionId;
                $user->save();
                return $next($request);
            }
            
            // If current session matches, allow
            if ($user->current_session_id === $currentSessionId) {
                return $next($request);
            }
            
            // Check if stored session still exists in database
            $sessionExists = \Illuminate\Support\Facades\DB::table('sessions')
                ->where('id', $user->current_session_id)
                ->exists();

            if (!$sessionExists) {
                // Old session is dead, update to current
                $user->current_session_id = $currentSessionId;
                $user->save();
                return $next($request);
            }
            
            // Another active session exists
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'This account is already logged in on another device.'
                ], 401);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'This account is already logged in on another device. For security, only one active session is allowed.',
            ]);
        }

        return $next($request);
    }
}
