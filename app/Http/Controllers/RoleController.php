<?php

namespace App\Http\Controllers;

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

    public function index(Request $request): JsonResponse
    {
        $roles = $this->roleService->getFilteredRoles($request->all());
        return response()->json($roles);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|unique:roles,name|max:100',
            'type'        => 'required|integer',
            'description' => 'nullable|string|max:255',
        ]);

        $role = $this->roleService->createRole($data);

        return response()->json([
            'success' => true,
            'message' => 'Role yaratildi.',
            'data'    => $role
        ], 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json($role->load('permissions'));
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'description' => 'nullable|string|max:255',
            'type'        => 'required|integer',
        ]);

        $this->roleService->updateRole($role, $data);

        return response()->json([
            'success' => true,
            'message' => 'Role yangilandi.',
            'data'    => $role->refresh()
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $this->roleService->deleteRole($role);

        return response()->json([
            'success' => true,
            'message' => 'Role o\'chirildi.'
        ]);
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

        return response()->json([
            'success' => true,
            'message' => 'Ruxsatnomalar muvaffaqiyatli saqlandi.',
            'count'   => $count
        ]);
    }
}
