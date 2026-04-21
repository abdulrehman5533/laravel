<?php

namespace App\Http\Controllers\SecurityManager;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SecurityAuditLog;
use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::withCount('users')->paginate(15);

        return view('security-manager.roles.index', compact('roles'));
    }

    public function create()
    {
        $this->authorize('create', Role::class);

        return view('security-manager.roles.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => 'required|string|unique:roles|max:255',
            'slug' => 'required|string|unique:roles|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'session_timeout_minutes' => 'required|integer|min:5|max:1440',
        ]);

        $role = Role::create($validated);

        SecurityAuditLog::log(
            'role_created',
            'role_management',
            $role->name,
            "Role {$role->name} created"
        );

        return redirect()->route('security-manager.roles.show', $role)
            ->with('success', 'Role created successfully');
    }

    public function show(Role $role)
    {
        $this->authorize('view', $role);

        $role->load('permissions');
        $permissions = Permission::all();
        $resources = $permissions->groupBy('resource');

        return view('security-manager.roles.show', [
            'role' => $role,
            'permissions' => $permissions,
            'resources' => $resources,
        ]);
    }

    public function edit(Role $role)
    {
        $this->authorize('update', $role);

        return view('security-manager.roles.edit', compact('role'));
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'name' => 'required|string|unique:roles,name,'.$role->id.'|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'session_timeout_minutes' => 'required|integer|min:5|max:1440',
        ]);

        $role->update($validated);

        SecurityAuditLog::log(
            'role_updated',
            'role_management',
            $role->name,
            "Role {$role->name} updated"
        );

        return redirect()->route('security-manager.roles.show', $role)
            ->with('success', 'Role updated successfully');
    }

    public function assignPermission(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $permission = Permission::findOrFail($validated['permission_id']);

        if (! $role->permissions()->where('permission_id', $permission->id)->exists()) {
            $role->permissions()->attach($permission);

            SecurityAuditLog::log(
                'permission_assigned',
                'permission_management',
                $permission->name,
                "Permission {$permission->name} assigned to role {$role->name}"
            );
        }

        return redirect()->back()->with('success', 'Permission assigned successfully');
    }

    public function revokePermission(Request $request, Role $role, Permission $permission)
    {
        $this->authorize('update', $role);

        $role->permissions()->detach($permission);

        SecurityAuditLog::log(
            'permission_revoked',
            'permission_management',
            $permission->name,
            "Permission {$permission->name} revoked from role {$role->name}"
        );

        return redirect()->back()->with('success', 'Permission revoked successfully');
    }

    public function permissionMatrix()
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::with('permissions')->get();
        $permissionsByResource = Permission::all()->groupBy('resource');

        // Calculate coverage for radar chart
        $coverage = [];
        $resources = $permissionsByResource->keys();

        foreach ($roles as $role) {
            $roleData = [];
            foreach ($resources as $resource) {
                $totalInDomain = $permissionsByResource[$resource]->count();
                $roleInDomain = $role->permissions->where('resource', $resource)->count();
                $roleData[] = $totalInDomain > 0 ? round(($roleInDomain / $totalInDomain) * 100) : 0;
            }
            $coverage[$role->name] = $roleData;
        }

        return view('security-manager.roles.permission-matrix', [
            'roles' => $roles,
            'permissionsByResource' => $permissionsByResource,
            'coverage' => $coverage,
            'resources' => $resources,
        ]);
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        SecurityAuditLog::log(
            'permissions_synced',
            'permission_management',
            $role->name,
            "Permissions synchronized for role {$role->name}"
        );

        return redirect()->back()->with('success', 'Permissions updated successfully');
    }

    public function togglePermission(Request $request, Role $role)
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'permission_id' => 'required|exists:permissions,id',
        ]);

        $role->permissions()->toggle($validated['permission_id']);

        $permission = Permission::find($validated['permission_id']);
        $action = $role->permissions()->where('permission_id', $permission->id)->exists() ? 'assigned' : 'revoked';

        SecurityAuditLog::log(
            'permission_'.$action,
            'permission_management',
            $role->name,
            "Permission {$permission->name} {$action} for role {$role->name}"
        );

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permission '.$action.' successfully',
                'action' => $action,
            ]);
        }

        return redirect()->back()->with('success', 'Permission '.$action.' successfully');
    }

    public function clone(Role $role)
    {
        $this->authorize('create', Role::class);

        $newRole = $role->replicate();
        $newRole->name = $role->name.' (Copy)';
        $newRole->slug = $role->slug.'-copy';
        $newRole->save();

        // Clone permissions
        $newRole->permissions()->sync($role->permissions->pluck('id'));

        SecurityAuditLog::log(
            'role_cloned',
            'role_management',
            $newRole->name,
            "Role {$role->name} cloned into {$newRole->name}"
        );

        return redirect()->route('security-manager.roles.index')
            ->with('success', 'Role cloned successfully');
    }
}
