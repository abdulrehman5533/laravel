<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EnforceIdleTimeout
{
    // Idle timeout in seconds (30 minutes)
    protected $timeout = 1800; // 30 minutes

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $lastActivity = session('last_activity_time');
            $now = time();
            if ($lastActivity && ($now - $lastActivity > $this->timeout)) {
                $sessionId = $request->session()->getId();
                $userId = Auth::id();
                // Mark session as terminated in custom table if used
                if (class_exists('App\\Models\\UserSession')) {
                    \App\Models\UserSession::where('user_id', $userId)
                        ->where('session_id', $sessionId)
                        ->active()
                        ->update([
                            'terminated_at' => now(),
                            'termination_reason' => 'Idle timeout',
                        ]);
                }
                // Remove session from DB (for database driver)
                DB::table(config('session.table', 'sessions'))->where('id', $sessionId)->delete();
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $cookieName = config('session.cookie');
                setcookie($cookieName, '', time() - 3600, '/', config('session.domain', null), config('session.secure', false), true);
                session()->regenerate();
                return redirect()->route('login')->with('status', 'Session expired due to inactivity. Please login again.');
            }
            session(['last_activity_time' => $now]);
        }
        return $next($request);
    }
}
