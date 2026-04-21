<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Don't update activity for heartbeat or logout AJAX requests
        if ($request->routeIs('api.heartbeat') || $request->routeIs('api.session-logout')) {
            return $response;
        }

        if (Auth::check()) {
            $sessionId = $request->session()->getId();
            $userId = Auth::id();
            $now = now();

            // Update standard Laravel sessions table
            DB::table('sessions')
                ->where('id', $sessionId)
                ->update([
                    'user_id' => $userId,
                    'last_activity' => time(),
                ]);

            // Update or Create custom user_sessions table for Security Manager
            $sessionExists = DB::table('user_sessions')
                ->where('user_id', $userId)
                ->where('session_id', $sessionId)
                ->exists();

            if ($sessionExists) {
                DB::table('user_sessions')
                    ->where('user_id', $userId)
                    ->where('session_id', $sessionId)
                    ->update([
                        'last_activity_at' => $now,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('user_sessions')->insert([
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                    'login_at' => $now,
                    'last_activity_at' => $now,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return $response;
    }
}
