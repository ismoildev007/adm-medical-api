<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $permissionService
    ) {}

    /**
     * Display a listing of permissions with optimal search.
     */
    public function index(Request $request)
    {
        $permissions = $this->permissionService->getFilteredPermissions($request->all());

        if ($request->ajax()) {
            return response()->json($permissions);
        }

        return view('admin.permissions.index', compact('permissions'));
    }
}
