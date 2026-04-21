<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            $dashboardRoute = null;
            if (method_exists($user, 'getDashboardRoute')) {
                $dashboardRoute = $user->getDashboardRoute();
            } elseif (property_exists($user, 'dashboardRoute')) {
                $dashboardRoute = $user->dashboardRoute;
            }
            if ($dashboardRoute) {
                return redirect()->intended(route($dashboardRoute, absolute: false));
            }
            return redirect()->intended('/dashboard');
        }

        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Clear Spatie permission cache for this user to ensure fresh permissions are loaded
        if (app()->bound(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }

        // Store the new session ID in the user record to enforce single login
        $user = Auth::user();
        $user->current_session_id = $request->session()->getId();
        if ($user instanceof Model) {
            $user->save();
        }

        // Initialize security timestamps
        $request->session()->put('last_activity_time', time());
        $request->session()->put('last_heartbeat_time', time());

        $dashboardRoute = null;
        if (method_exists($user, 'getDashboardRoute')) {
            $dashboardRoute = $user->getDashboardRoute();
        } elseif (property_exists($user, 'dashboardRoute')) {
            $dashboardRoute = $user->dashboardRoute;
        }
        if ($dashboardRoute) {
            return redirect()->intended(route($dashboardRoute, absolute: false));
        }
        return redirect()->intended('/dashboard');
    }

    /**
     * Handle logout when a browser tab or window is closed via AJAX.
     * Uses navigator.sendBeacon to ensure the session is terminated.
     */
    public function tabCloseLogout(Request $request): \Illuminate\Http\JsonResponse
    {
        if (Auth::check()) {
            $user = Auth::user();
            $sessionId = $request->session()->getId();

            // Delete session from database immediately
            DB::table(config('session.table', 'sessions'))->where('id', $sessionId)->delete();

            // Clear current_session_id
            $user->current_session_id = null;
            if ($user instanceof Model) {
                $user->save();
            }

            // Logout from Guard
            Auth::guard('web')->logout();

            // Invalidate session
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(['status' => 'success', 'logged_out' => true]);
        }

        return response()->json(['status' => 'no_session', 'logged_out' => false]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        if (Auth::check()) {
            $sessionId = $request->session()->getId();
            $userId = Auth::id();

            // Log logout event
            \App\Models\SecurityAuditLog::log(
                'logout',
                'auth',
                'User '.Auth::user()->name,
                'User logged out successfully'
            );

            // Mark custom session as terminated
            \App\Models\UserSession::where('user_id', $userId)
                ->where('session_id', $sessionId)
                ->active()
                ->update([
                    'terminated_at' => now(),
                    'termination_reason' => 'User logged out',
                ]);

            // Remove session from DB (for database driver)
            DB::table(config('session.table', 'sessions'))->where('id', $sessionId)->delete();

            // Clear single session ID from user
            $user = Auth::user();
            $user->current_session_id = null;
            if ($user instanceof Model) {
                $user->save();
            }
        }

        Auth::guard('web')->logout();

        // Invalidate and regenerate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Remove session cookie
        $cookieName = config('session.cookie');
        setcookie($cookieName, '', time() - 3600, '/', config('session.domain', null), config('session.secure', false), true);

        // Prevent session fixation
        session()->regenerate();

        if ($request->has('inactive')) {
            return redirect()->route('login')->with('status', 'Session expired due to inactivity. Please login again.');
        }

        return redirect()->route('login');
    }
}
