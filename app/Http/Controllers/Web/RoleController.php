<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::withCount('permissions');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('permission')) {
            $query->whereHas('permissions', fn($q) => $q->where('name', $request->permission));
        }

        if (!auth()->user()->hasRole('superadmin')) {
            $query->where('name', '!=', 'superadmin');
        }

        $roles = $query->get();
        $permissions = Permission::orderBy('name')->get();

        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|unique:roles,name|max:100',
            'type'        => 'required|integer',
            'description' => 'nullable|string|max:255',
        ]);

        $data['created_by'] = auth()->id();
        Role::create($data);

        return back()->with('success', 'Role yaratildi.');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'description' => 'nullable|string|max:255',
            'type'        => 'required|integer',
        ]);

        $data['updated_by'] = auth()->id();
        $role->update($data);

        return back()->with('success', 'Role yangilandi.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return back()->with('success', 'Role o\'chirildi.');
    }

    /**
     * Get all route-based permissions for the permission sync modal.
     */
    public function permissionsForRole(Role $role): JsonResponse
    {
        $allPermissions = $this->getAllRoutePermissions();

        $assigned = $role->permissions()->pluck('name')->toArray();

        return response()->json([
            'all'      => $allPermissions,
            'assigned' => $assigned,
        ]);
    }

    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'permissions'   => 'array',
            'permissions.*' => 'string',
        ]);

        $permissionNames = $data['permissions'] ?? [];

        foreach ($permissionNames as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $role->permissions()->detach();
        foreach ($permissionNames as $name) {
            $role->permissions()->attach($name);
        }

        return response()->json(['success' => true, 'count' => count($permissionNames)]);
    }

    /**
     * Derive permission name list from all named API routes.
     */
    private function getAllRoutePermissions(): array
    {
        $routes = Route::getRoutes()->getRoutesByName();
        $names  = [];
        foreach ($routes as $name => $route) {
            if (str_starts_with($name, 'sanctum')
                || str_starts_with($name, 'ignition')
                || str_starts_with($name, 'debugbar')
                || str_starts_with($name, 'web.')
            ) {
                continue;
            }
            $perm = str_replace(['.', '_', ' '], '-', $name);
            $names[] = $perm;
        }
        sort($names);
        return array_values(array_unique($names));
    }
}
