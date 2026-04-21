<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IdleSessionTimeout
{
    // Idle timeout in seconds (120 minutes)
    protected $timeout = 7200;

    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $now = time();
            $lastActivity = session('last_activity_time');

            // Only expire if last_activity_time is set AND timeout exceeded
            if ($lastActivity !== null && ($now - $lastActivity > $this->timeout)) {
                $sessionId = $request->session()->getId();
                DB::table(config('session.table', 'sessions'))->where('id', $sessionId)->delete();
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('status', 'Session expired due to inactivity. Please login again.');
            }

            session(['last_activity_time' => $now]);
        }
        return $next($request);
    }
}
