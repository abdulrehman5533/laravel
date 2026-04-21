<?php

namespace App\Http\Controllers\SecurityManager;

use App\Http\Controllers\Controller;
use App\Models\SecurityAuditLog;
use App\Models\UserSession;
use App\Services\Security\SessionPolicyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SecurityHeartbeatController extends Controller
{
    protected $sessionPolicy;

    public function __construct(SessionPolicyService $sessionPolicy)
    {
        $this->middleware('auth');
        $this->sessionPolicy = $sessionPolicy;
    }

    public function beat(Request $request)
    {
        $user = Auth::user();
        $globalSettings = $this->sessionPolicy->getGlobalSettings();

        $sessionLifeMinutes = $this->sessionPolicy->getSessionTimeout($user);
        $idleMinutes = $this->sessionPolicy->getIdleTimeout($user);
        $minimizeMinutes = $this->sessionPolicy->getMinimizeTimeout($user);
        $backgroundMinutes = $this->sessionPolicy->getBackgroundTimeout($user);

        return response()->json([
            'status' => 'active',
            'session_life' => $sessionLifeMinutes,
            'idle_timeout' => $idleMinutes,
            'minimize_timeout' => $minimizeMinutes,
            'background_timeout' => $backgroundMinutes,
            'warning_threshold' => $globalSettings['popup_warning_minutes'] ?? 5,
        ]);
    }

    public function logout(Request $request)
    {
        $reason = $request->input('reason', 'Session expired');
        $user = Auth::user();
        $sessionId = session()->getId();

        if ($user) {
            SecurityAuditLog::log(
                'session_expired',
                'auth',
                "User {$user->name}",
                "Session automatically terminated: {$reason}",
                ['reason' => $reason, 'session_id' => $sessionId]
            );

            UserSession::where('user_id', $user->id)
                ->where('session_id', $sessionId)
                ->active()
                ->update([
                    'terminated_at' => now(),
                    'termination_reason' => $reason,
                ]);

            // Clear single session ID from user
            $user->current_session_id = null;
            $user->save();
        }

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return response()->json(['status' => 'logged_out']);
    }
}
