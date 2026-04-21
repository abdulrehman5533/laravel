<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ValidateSessionInDatabase
{
    public function handle(Request $request, Closure $next)
    {
        // Temporarily disabled - causing issues with session regeneration
        return $next($request);
        
        /*
        if (Auth::check()) {
            $sessionId = $request->session()->getId();
            $sessionTable = config('session.table', 'sessions');
            $exists = DB::table($sessionTable)->where('id', $sessionId)->exists();
            if (!$exists) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $cookieName = config('session.cookie');
                setcookie($cookieName, '', time() - 3600, '/', config('session.domain', null), config('session.secure', false), true);
                session()->regenerate();
                return redirect()->route('login')->with('status', 'Session invalid or expired. Please login again.');
            }
        }
        return $next($request);
        */
    }
}
