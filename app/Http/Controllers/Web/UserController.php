<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('firstname', 'like', "%$search%")
                  ->orWhere('lastname', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.name', $request->role);
            });
        }

        if ($request->filled('permission')) {
            $query->whereHas('roles.permissions', function($q) use ($request) {
                $q->where('permissions.name', $request->permission);
            });
        }

        $users = $query->get();
        $roles = Role::orderBy('name')->get();
        $permissions = Permission::orderBy('name')->get();

        $allProjects = \OwenIt\Auditing\Models\Audit::select('project_name')
            ->distinct()
            ->whereNotNull('project_name')
            ->orderBy('project_name')
            ->pluck('project_name');

        return view('admin.users.index', compact('users', 'roles', 'permissions', 'allProjects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname'  => 'required|string|max:100',
            'username'  => 'required|string|max:100|unique:users,username',
            'password'  => 'required|string|min:6',
            'roles'     => 'required|array',
            'roles.*'   => 'string|exists:roles,name',
            'projects'  => 'nullable|array',
            'projects.*'=> 'string',
        ]);

        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'username'  => $data['username'],
            'password'  => Hash::make($data['password']),
            'project_permission' => $data['projects'] ?? [],
            'created_by' => auth()->id(),
        ]);

        $user->roles()->attach($data['roles']);

        return back()->with('success', 'Foydalanuvchi yaratildi.');
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'roles'     => 'required|array',
            'roles.*'   => 'string|exists:roles,name',
            'projects'  => 'nullable|array',
            'projects.*'=> 'string',
        ]);

        $user->roles()->sync($data['roles']);
        $user->update(['project_permission' => $data['projects'] ?? []]);

        return back()->with('success', 'Ma\'lumotlar yangilandi.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Foydalanuvchi o\'chirildi.');
    }
}
