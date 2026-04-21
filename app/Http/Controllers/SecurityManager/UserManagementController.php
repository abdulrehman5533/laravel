<?php

namespace App\Http\Controllers\SecurityManager;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Role;
use App\Models\SecurityAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with(['role', 'sessions'])
            ->paginate(15);

        return view('security-manager.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        if ($tenant && $tenant->reachedLimit('users')) {
            return redirect()->route('security-manager.users.index')
                ->with('error', 'User limit reached for your current plan. Please upgrade to add more users.');
        }

        $roles = Role::where('is_active', true)->get();
        $branches = Branch::all();

        return view('security-manager.users.create', [
            'roles' => $roles,
            'branches' => $branches,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;
        if ($tenant && $tenant->reachedLimit('users')) {
            return redirect()->route('security-manager.users.index')
                ->with('error', 'User limit reached for your current plan. Please upgrade to add more users.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role_id' => 'required|exists:roles,id',
            'branch_id' => 'nullable|exists:branches,id',
            'phone' => 'nullable|string|max:20',
            'user_status' => 'required|in:active,suspended',
            'force_password_change' => 'boolean',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'session_timeout_minutes' => 'nullable|integer|min:0',
            'allowed_login_start' => 'nullable|date_format:H:i',
            'allowed_login_end' => 'nullable|date_format:H:i',
            'allowed_days' => 'nullable|array',
            'authorized_until' => 'nullable|date|after_or_equal:today',
        ]);

        if ($request->hasFile('avatar')) {
            $image = $request->file('avatar');
            $filename = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
            $path = 'uploads/users/'.$filename;

            if (! file_exists(public_path('uploads/users'))) {
                mkdir(public_path('uploads/users'), 0755, true);
            }

            $image->move(public_path('uploads/users'), $filename);
            $validated['avatar'] = $path;
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $validated['user_status'] === 'active';


        $user = User::create($validated);
        $currentUser = Auth::user();
        SecurityAuditLog::log(
            'user_created',
            'user_management',
            "User {$user->name}",
            "User {$user->name} created by ".($currentUser ? $currentUser->name : 'system')
        );

        return redirect()->route('security-manager.users.show', $user)
            ->with('success', 'User created successfully');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['role', 'sessions', 'auditLogs']);
        $activeSessions = $user->activeSessions()->get();

        return view('security-manager.users.show', [
            'user' => $user,
            'activeSessions' => $activeSessions,
            'recentActivity' => $user->auditLogs()->recent(7)->latest()->get(),
        ]);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::where('is_active', true)->get();
        $branches = Branch::all();

        return view('security-manager.users.edit', [
            'user' => $user,
            'roles' => $roles,
            'branches' => $branches,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'role_id' => 'required|exists:roles,id',
            'user_status' => 'required|in:active,suspended,locked',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'remove_avatar' => 'nullable|boolean',
            'session_timeout_minutes' => 'nullable|integer|min:0',
            'allowed_login_start' => 'nullable', // Allow HH:mm or HH:mm:ss
            'allowed_login_end' => 'nullable',
            'allowed_days' => 'nullable|array',
            'authorized_until' => 'nullable|date',
        ]);

        if ($request->has('remove_avatar') && $request->remove_avatar) {
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }
            $validated['avatar'] = null;
        } elseif ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && file_exists(public_path($user->avatar))) {
                unlink(public_path($user->avatar));
            }

            $image = $request->file('avatar');
            $filename = time().'_'.uniqid().'.'.$image->getClientOriginalExtension();
            $path = 'uploads/users/'.$filename;

            // Ensure directory exists
            if (! file_exists(public_path('uploads/users'))) {
                mkdir(public_path('uploads/users'), 0755, true);
            }

            $image->move(public_path('uploads/users'), $filename);
            $validated['avatar'] = $path;
        }

        $validated['is_active'] = $validated['user_status'] === 'active';
        $user->update($validated);

        SecurityAuditLog::log(
            'user_updated',
            'user_management',
            "User {$user->name}",
            "User {$user->name} profile updated"
        );

        return redirect()->route('security-manager.users.show', $user)
            ->with('success', 'User updated successfully');
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->authorize('update', $user);

        try {
            $validated = $request->validate([
                'new_password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Pass user_id to old() for modal reopening
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput(array_merge($request->all(), ['user_id' => $user->id]));
        }


        $user->update([
            'password' => Hash::make($validated['new_password']),
            'force_password_change' => true,
        ]);
        $currentUser = Auth::user();
        SecurityAuditLog::log(
            'password_reset',
            'user_management',
            "User {$user->name}",
            'Password reset by '.($currentUser ? $currentUser->name : 'system')
        );

        return redirect()->back()
            ->with('success', 'Password reset successfully. User will be prompted to change it.');
    }

    public function suspend(User $user)
    {
        $this->authorize('update', $user);


        $user->update([
            'user_status' => 'suspended',
            'is_active' => false,
        ]);
        $currentUser = Auth::user();
        SecurityAuditLog::log(
            'user_suspended',
            'user_management',
            "User {$user->name}",
            'User suspended by '.($currentUser ? $currentUser->name : 'system')
        );

        return redirect()->back()->with('success', 'User suspended successfully');
    }

    public function unlock(User $user)
    {
        $this->authorize('update', $user);


        $user->update([
            'user_status' => 'active',
            'is_active' => true,
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
        $currentUser = Auth::user();
        SecurityAuditLog::log(
            'user_unlocked',
            'user_management',
            "User {$user->name}",
            'User unlocked by '.($currentUser ? $currentUser->name : 'system')
        );

        return redirect()->back()->with('success', 'User unlocked successfully');
    }

    public function activate(User $user)
    {
        $this->authorize('update', $user);


        $user->update([
            'user_status' => 'active',
            'is_active' => true,
        ]);
        $currentUser = Auth::user();
        SecurityAuditLog::log(
            'user_activated',
            'user_management',
            "User {$user->name}",
            'User activated by '.($currentUser ? $currentUser->name : 'system')
        );

        return redirect()->back()->with('success', 'User activated successfully');
    }
}
