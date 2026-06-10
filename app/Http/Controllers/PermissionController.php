<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {}

    /**
     * Display a paginated listing of permissions.
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $permissions = $this->permissionService->getFilteredPermissions($request->all());

        return response()->json($permissions);
    }

    /**
     * Get a simple array list of all permissions (unpaginated).
     */
    public function list(): \Illuminate\Http\JsonResponse
    {
        $permissions = \App\Models\Permission::orderBy('name')->pluck('name');
        
        return response()->json([
            'data' => $permissions
        ]);
    }
}
