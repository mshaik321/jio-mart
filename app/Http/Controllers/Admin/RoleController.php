<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(private AuditLogService $auditLog) {}

    public function index()
    {
        $this->authorize('roles.manage');

        $roles = Role::query()->with('permissions')->orderBy('name')->get();

        return view('admin.roles.index', compact('roles'));
    }

    public function edit(Role $role)
    {
        $this->authorize('roles.manage');

        $permissions = Permission::query()->orderBy('name')->get();

        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $this->authorize('roles.manage');

        $data = $request->validate([
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        $oldPermissions = $role->permissions->pluck('name')->sort()->values()->all();

        $role->syncPermissions($data['permissions'] ?? []);

        $this->auditLog->log(
            $request->user(),
            'role.permissions_changed',
            $role,
            ['permissions' => $oldPermissions],
            ['permissions' => collect($data['permissions'] ?? [])->sort()->values()->all()],
        );

        return redirect()->route('admin.roles.index')->with('status', "Permissions updated for {$role->name}.");
    }
}
