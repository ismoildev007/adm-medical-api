<?php

namespace App\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UserUpsertRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getFilteredUsers($request->all());
        return response()->json($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());
        return response()->json(['success' => true, 'data' => $user->load('roles')], 201);
    }

    public function update(int $id, UserUpsertRequest $request): JsonResponse
    {
        $user = User::findOrFail($id);

        if ($user->type == UserTypeEnum::SYSTEM) {
            return response()->json(['message' => "You can't update system user"], 401);
        }

        $data = $request->validated();
        unset($data['username']);

        $this->userService->update($user, $data);

        return response()->json(['success' => true, 'data' => $user->fresh()->load('roles')]);
    }

    public function updateRole(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $data = $request->validate([
            'roles'   => 'nullable|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $this->userService->updateUserDetails($user, $data);

        return response()->json(['success' => true, 'data' => $user->fresh()->load('roles')]);
    }

    public function findFromLdap(Request $request): JsonResponse
    {
        $result = $this->userService->findFromLdap(strtolower($request->input('username', '')));
        return response()->json($result);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $this->userService->delete($user);
        return response()->json(['success' => true]);
    }
}
