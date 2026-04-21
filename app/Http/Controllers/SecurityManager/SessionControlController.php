<?php

namespace App\Http\Controllers\SecurityManager;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SecurityAuditLog;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\Security\SessionPolicyService;
use Illuminate\Http\Request;

class SessionControlController extends Controller
{
    protected $sessionPolicy;

    public function __construct(SessionPolicyService $sessionPolicy)
    {
        $this->middleware('auth');
        $this->sessionPolicy = $sessionPolicy;
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $globalSettings = $this->sessionPolicy->getGlobalSettings();
        $roles = Role::withCount('users')->get();
        $users = User::with('role')->paginate(15);

        return view('security-manager.session-control.index', compact('globalSettings', 'roles', 'users'));
    }

    public function updateGlobal(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'default_session_timeout' => 'required|integer|min:1',
            'default_idle_timeout' => 'required|integer|min:1',
            'default_minimize_timeout' => 'required|integer|min:1',
            'default_background_timeout' => 'required|integer|min:1',
            'enable_idle_logout' => 'boolean',
            'enable_minimize_logout' => 'boolean',
            'enable_background_logout' => 'boolean',
            'popup_warning_minutes' => 'required|integer|min:0',
            'auto_extend_session' => 'boolean',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::set($key, $value, 'security_session');
        }

        SecurityAuditLog::log(
            'global_session_policy_updated',
            'security',
            'Global Session Policy',
            'Updated global session control parameters'
        );

        return redirect()->back()->with('success', 'Global session settings updated successfully');
    }

    public function updateRole(Request $request, Role $role)
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'session_timeout_minutes' => 'nullable|integer|min:1',
            'idle_timeout_minutes' => 'nullable|integer|min:1',
            'minimize_timeout_minutes' => 'nullable|integer|min:1',
            'background_timeout_minutes' => 'nullable|integer|min:1',
            'enable_idle_logout' => 'boolean',
            'enable_minimize_logout' => 'boolean',
            'enable_background_logout' => 'boolean',
        ]);

        $role->update($validated);

        SecurityAuditLog::log(
            'role_session_policy_updated',
            'security',
            "Role: {$role->name}",
            "Updated session policy for role {$role->name}"
        );

        return redirect()->back()->with('success', "Session policy for {$role->name} updated successfully");
    }

    public function updateUser(Request $request, User $user)
    {
        $this->authorize('viewAny', User::class);

        $validated = $request->validate([
            'session_timeout_minutes' => 'nullable|integer|min:1',
            'idle_timeout_minutes' => 'nullable|integer|min:1',
            'minimize_timeout_minutes' => 'nullable|integer|min:1',
            'background_timeout_minutes' => 'nullable|integer|min:1',
            'enable_idle_logout' => 'nullable|boolean',
            'enable_minimize_logout' => 'nullable|boolean',
            'enable_background_logout' => 'nullable|boolean',
        ]);

        $user->update($validated);

        SecurityAuditLog::log(
            'user_session_policy_updated',
            'security',
            "User: {$user->name}",
            "Updated specific session policy for user {$user->name}"
        );

        return redirect()->back()->with('success', "Specific session policy for {$user->name} updated successfully");
    }
}
