<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions with optimal search.
     */
    public function index(Request $request)
    {
        $query = Permission::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $permissions = $query->orderBy('name')->paginate(50)->withQueryString();

        if ($request->ajax()) {
            return response()->json($permissions);
        }

        return view('admin.permissions.index', compact('permissions'));
    }
}
