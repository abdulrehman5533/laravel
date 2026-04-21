<?php

namespace App\Http\Controllers\SecurityManager;

use App\Http\Controllers\Controller;
use App\Models\SecurityAuditLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class SecurityManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $this->authorize('viewAny', User::class);

        $totalUsers = User::count();
        $activeUsers = User::where('user_status', 'active')->count();
        $suspendedUsers = User::where('user_status', 'suspended')->count();
        $lockedUsers = User::where('user_status', 'locked')->count();
        $inactiveUsers = $totalUsers - $activeUsers - $suspendedUsers - $lockedUsers;

        $activeSessions = UserSession::active()->count();
        $recentLogins = SecurityAuditLog::where('action', 'login')
            ->recent(7)
            ->count();

        $securityAlerts = DB::table('users')
            ->where('failed_login_attempts', '>', 0)
            ->orWhereNotNull('locked_until')
            ->count();

        $recentLogs = SecurityAuditLog::with('user')
            ->recent(1)
            ->latest()
            ->limit(10)
            ->get();

        $usersByStatus = User::select('user_status', DB::raw('count(*) as count'))
            ->groupBy('user_status')
            ->get();

        $loginTrend = SecurityAuditLog::where('action', 'login')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->get();

        return view('security-manager.dashboard', [
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
            'suspendedUsers' => $suspendedUsers,
            'lockedUsers' => $lockedUsers,
            'inactiveUsers' => $inactiveUsers,
            'activeSessions' => $activeSessions,
            'recentLogins' => $recentLogins,
            'securityAlerts' => $securityAlerts,
            'recentLogs' => $recentLogs,
            'usersByStatus' => $usersByStatus,
            'loginTrend' => $loginTrend,
        ]);
    }

    public function showChangePassword()
    {
        return view('security-manager.password.change');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()->min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $user = auth()->user();
        
        // Update user record
        $user->update([
            'password' => Hash::make($request->password),
            'force_password_change' => false,
            'password_changed_at' => now(),
        ]);

        // Log the security event
        SecurityAuditLog::log(
            'password_changed',
            'auth',
            "User {$user->name}",
            'User changed their password successfully'
        );

        // CRITICAL: Refresh the authentication session to sync the new password hash
        // This prevents the user from being logged out by session fixation protection
        // or by the AuthenticateSession middleware if enabled.
        auth()->login($user);
        
        // Clear Spatie permission cache for this user to ensure fresh permissions are loaded
        if (app()->bound(\Spatie\Permission\PermissionRegistrar::class)) {
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
        
        // Regenerate the session ID for security
        $request->session()->regenerate();
        
        // Initialize security timestamps to prevent immediate timeout
        $request->session()->put('last_activity_time', time());
        $request->session()->put('last_heartbeat_time', time());
        
        // Update the current session ID in the user record to maintain single session enforcement
        $user->current_session_id = $request->session()->getId();
        $user->save();

        // Redirect to the role-specific dashboard immediately to avoid middleware-based double redirection
        $dashboardRoute = $user->getDashboardRoute();
        
        return redirect()->route($dashboardRoute)->with('success', 'Password updated successfully. Your session has been refreshed.');
    }

    public function settings()
    {
        $this->authorize('viewAny', User::class);

        $settings = [
            'password_min_length' => SystemSetting::get('password_min_length', 8),
            'max_failed_attempts' => SystemSetting::get('max_failed_attempts', 5),
            'lockout_duration' => SystemSetting::get('lockout_duration', 30),
            'session_lifetime' => SystemSetting::get('session_lifetime', 120),
            'multi_session' => SystemSetting::get('multi_session', false),
            'audit_retention' => SystemSetting::get('audit_retention', 365),
        ];

        return view('security-manager.settings.index', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'password_min_length' => 'required|integer|min:6|max:32',
            'max_failed_attempts' => 'required|integer|min:3|max:20',
            'lockout_duration' => 'required|integer|min:1|max:1440',
            'session_lifetime' => 'required|integer|min:15|max:1440',
            'multi_session' => 'boolean',
            'audit_retention' => 'required|integer|min:30|max:3650',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value, 'security');
        }

        SecurityAuditLog::log(
            'settings_updated',
            'security',
            'Global Security Policy',
            'Updated global security parameters and protocols'
        );

        return redirect()->back()->with('success', 'Security policies updated successfully');
    }

    public function stats()
    {
        $this->authorize('viewAny', User::class);

        $totalUsers = User::count();
        $activeUsers = User::where('user_status', 'active')->count();
        $suspendedUsers = User::where('user_status', 'suspended')->count();
        $lockedUsers = User::where('user_status', 'locked')->count();

        $activeSessions = UserSession::active()->count();
        $recentLogins = SecurityAuditLog::where('action', 'login')
            ->recent(7)
            ->count();

        $securityAlerts = DB::table('users')
            ->where('failed_login_attempts', '>', 0)
            ->orWhereNotNull('locked_until')
            ->count();

        $loginTrend = SecurityAuditLog::where('action', 'login')
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->get();

        $riskyUsers = User::where('failed_login_attempts', '>', 0)
            ->orWhereNotNull('locked_until')
            ->orderBy('failed_login_attempts', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => [
                'totalUsers' => $totalUsers,
                'activeUsers' => $activeUsers,
                'suspendedUsers' => $suspendedUsers,
                'lockedUsers' => $lockedUsers,
                'inactiveUsers' => $totalUsers - $activeUsers - $suspendedUsers - $lockedUsers,
                'activeSessions' => $activeSessions,
                'recentLogins' => $recentLogins,
                'securityAlerts' => $securityAlerts,
            ],
            'loginTrend' => $loginTrend,
            'riskyUsers' => $riskyUsers,
        ]);
    }
}
