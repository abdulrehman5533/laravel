<?php

namespace App\Http\Middleware;

use App\Services\Security\SessionPolicyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSecurityEnforcement
{
    protected $sessionPolicy;

    public function __construct(SessionPolicyService $sessionPolicy)
    {
        $this->sessionPolicy = $sessionPolicy;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // 1. Check account status
            if ($user->isLocked() || $user->isSuspended()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors(['email' => 'Your account is no longer active.']);
            }

            // 2. Check Authorized Access Period
            if (method_exists($user, 'isWithinAuthorizedPeriod') && ! $user->isWithinAuthorizedPeriod()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Access denied: You are currently outside your authorized access window.');
            }

            // 3. Check Force Password Change
            if ($user->force_password_change && ! $request->routeIs('profile.password.*') && ! $request->routeIs('logout')) {
                return redirect()->route('profile.password.change')
                    ->with('warning', 'You must change your password before continuing.');
            }

            // 4. Session Timeout Tracking
            $timeoutMinutes = $this->sessionPolicy->getIdleTimeout($user);

            if ($timeoutMinutes > 0) {
                $now = time();
                $lastActivity = $request->session()->get('last_activity_time');

                // Check Idle Timeout
                // We add a 30-second grace period to avoid race conditions with heartbeats
                if ($lastActivity && ($now - $lastActivity > ($timeoutMinutes * 60 + 30))) {
                    return $this->terminateSession($request, 'idle_timeout', 'Session expired due to inactivity.');
                }

                // Update activity timestamp
                $request->session()->put('last_activity_time', $now);
            }
        }

        return $next($request);
    }

    /**
     * Terminate session for security reasons.
     */
    protected function terminateSession(Request $request, string $reason, string $message): Response
    {
        $user = Auth::user();
        $sessionId = $request->session()->getId();

        // Log the event
        \App\Models\SecurityAuditLog::log(
            'session_expired',
            'auth',
            'User '.($user ? $user->name : 'Unknown'),
            "Session terminated: {$reason}. {$message}",
            ['reason' => $reason, 'session_id' => $sessionId]
        );

        // Mark UserSession as terminated if model exists
        if ($user) {
            \App\Models\UserSession::where('user_id', $user->id)
                ->where('session_id', $sessionId)
                ->active()
                ->update([
                    'terminated_at' => now(),
                    'termination_reason' => $reason,
                ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['status' => 'expired', 'message' => $message], 401);
        }

        return redirect()->route('login')->with('status', $message);
    }
}
