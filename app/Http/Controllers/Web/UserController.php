<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(Request $request)
    {
        $users = $this->userService->getFilteredUsers($request->all());
        $formData = $this->userService->getUserFormData();

        return view('admin.users.index', array_merge(['users' => $users], $formData));
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

        $this->userService->createUser($data);

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

        $this->userService->updateRolesAndProjects($user, $data);

        return back()->with('success', 'Ma\'lumotlar yangilandi.');
    }

    public function destroy(User $user)
    {
        $this->userService->delete($user);
        return back()->with('success', 'Foydalanuvchi o\'chirildi.');
    }
}
