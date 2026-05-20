<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RolePermissionController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(): View
    {
        $roles = Role::query()->orderBy('name')->with('permissions')->get();
        $permissions = Permission::query()->orderBy('group')->orderBy('name')->get()->groupBy('group');

        return view('admin.role-permissions.index', compact('roles', 'permissions'));
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        abort_if($role->slug === \App\Models\User::ROLE_SUPER_ADMIN, 403);

        $ids = $request->validate([
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ])['permission_ids'] ?? [];

        $role->permissions()->sync($ids);

        $this->activityLog->log('role.permissions_updated', $role, ['permission_ids' => $ids]);

        return response()->json(['message' => 'Permissions updated']);
    }
}
