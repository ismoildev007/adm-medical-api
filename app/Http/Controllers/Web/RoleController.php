<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService
    ) {}

    public function index(Request $request)
    {
        $roles = $this->roleService->getFilteredRoles($request->all());
        $permissions = $this->roleService->getAllPermissions();

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|unique:roles,name|max:100',
            'type'        => 'required|integer',
            'description' => 'nullable|string|max:255',
        ]);

        $this->roleService->createRole($data);

        return back()->with('success', 'Role yaratildi.');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'description' => 'nullable|string|max:255',
            'type'        => 'required|integer',
        ]);

        $this->roleService->updateRole($role, $data);

        return back()->with('success', 'Role yangilandi.');
    }

    public function destroy(Role $role)
    {
        $this->roleService->deleteRole($role);
        return back()->with('success', 'Role o\'chirildi.');
    }

    public function permissionsForRole(Role $role): JsonResponse
    {
        return response()->json($this->roleService->getPermissionsForRole($role));
    }

    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'permissions'   => 'array',
            'permissions.*' => 'string',
        ]);

        $count = $this->roleService->syncPermissions($role, $data['permissions'] ?? []);

        return response()->json(['success' => true, 'count' => $count]);
    }
}
