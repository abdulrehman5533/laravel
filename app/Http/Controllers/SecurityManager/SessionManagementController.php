<?php

namespace App\Http\Controllers\SecurityManager;

use App\Http\Controllers\Controller;
use App\Models\SecurityAuditLog;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SessionManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $activeSessions = UserSession::active()
            ->with('user')
            ->latest('last_activity_at')
            ->paginate(20);

        $sessionStats = [
            'total_active' => UserSession::active()->count(),
            'total_users_online' => UserSession::active()->distinct('user_id')->count('user_id'),
            'total_terminated_today' => UserSession::whereDate('terminated_at', today())->count(),
        ];

        return view('security-manager.sessions.index', [
            'activeSessions' => $activeSessions,
            'sessionStats' => $sessionStats,
        ]);
    }

    public function userSessions(User $user)
    {
        $this->authorize('view', $user);

        $user->load('sessions');
        $activeSessions = $user->activeSessions()->latest('last_activity_at')->get();
        $terminatedSessions = $user->sessions()->whereNotNull('terminated_at')->latest('terminated_at')->limit(10)->get();

        return view('security-manager.sessions.user-sessions', [
            'user' => $user,
            'activeSessions' => $activeSessions,
            'terminatedSessions' => $terminatedSessions,
        ]);
    }

    public function terminate(UserSession $session)
    {
        $this->authorize('viewAny', User::class);

        $user = $session->user;
        $session->terminate('Manually terminated by '.auth()->user()->name);

        // Also delete from Laravel's default sessions table to effectively log out the user
        DB::table('sessions')->where('id', $session->session_id)->delete();

        SecurityAuditLog::log(
            'session_terminated',
            'session_management',
            "User {$user->name}",
            'Session terminated by '.auth()->user()->name,
            ['session_id' => $session->session_id]
        );

        return redirect()->back()->with('success', 'Session terminated successfully');
    }

    public function terminateAll(User $user)
    {
        $this->authorize('viewAny', User::class);

        $activeSessionIds = $user->activeSessions()->pluck('session_id')->toArray();
        $count = count($activeSessionIds);

        $user->activeSessions()->update([
            'terminated_at' => now(),
            'termination_reason' => 'All sessions terminated by '.auth()->user()->name,
        ]);

        // Also delete from Laravel's default sessions table
        if ($count > 0) {
            DB::table('sessions')->whereIn('id', $activeSessionIds)->delete();
        }

        SecurityAuditLog::log(
            'all_sessions_terminated',
            'session_management',
            "User {$user->name}",
            "All {$count} sessions terminated by ".auth()->user()->name
        );

        return redirect()->back()->with('success', "All {$count} sessions terminated successfully");
    }

    public function sessionHistory(User $user)
    {
        $this->authorize('view', $user);

        $sessions = $user->sessions()
            ->latest('login_at')
            ->paginate(15);

        return view('security-manager.sessions.history', [
            'user' => $user,
            'sessions' => $sessions,
        ]);
    }

    public function configureTimeout(Request $request, User $user)
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'timeout_minutes' => 'required|integer|min:5|max:1440',
        ]);

        if ($user->role) {
            $user->role->update(['session_timeout_minutes' => $validated['timeout_minutes']]);

            SecurityAuditLog::log(
                'session_timeout_configured',
                'session_management',
                "Role {$user->role->name}",
                "Session timeout set to {$validated['timeout_minutes']} minutes"
            );
        }

        return redirect()->back()->with('success', 'Session timeout configured successfully');
    }
}
