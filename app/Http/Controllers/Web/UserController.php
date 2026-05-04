<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
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

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $this->userService->createUser($data);

        return back()->with('success', 'Foydalanuvchi yaratildi.');
    }

    public function updateRole(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $this->userService->updateUserDetails($user, $data);

        return back()->with('success', 'Ma\'lumotlar yangilandi.');
    }

    public function destroy(User $user)
    {
        $this->userService->delete($user);
        return back()->with('success', 'Foydalanuvchi o\'chirildi.');
    }
}
