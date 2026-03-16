<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search filter (Firstname, Lastname, Username)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('firstname', 'like', "%$search%")
                  ->orWhere('lastname', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('roles.name', $request->role);
            });
        }

        // Permission filter
        if ($request->filled('permission')) {
            $query->whereHas('roles.permissions', function($q) use ($request) {
                $q->where('permissions.name', $request->permission);
            });
        }

        $users = $query->get();
        $roles = Role::orderBy('name')->get();
        $permissions = \App\Models\Permission::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'permissions'));
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
        ]);

        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'username'  => $data['username'],
            'password'  => Hash::make($data['password']),
            'created_by' => auth()->id(),
        ]);

        // Attach the roles
        $user->roles()->attach($data['roles']);

        return back()->with('success', 'Foydalanuvchi yaratildi.');
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate([
            'roles'   => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        // Sync roles (replaces existing with the new set)
        $user->roles()->sync($data['roles']);

        return back()->with('success', 'Role yangilandi.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('success', 'Foydalanuvchi o\'chirildi.');
    }
}
