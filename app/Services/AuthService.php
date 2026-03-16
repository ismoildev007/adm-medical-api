<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AuditService   $auditService,
    ) {}

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new \Illuminate\Validation\ValidationException(
                validator([], []),
                response()->json(['message' => 'The provided credentials are incorrect.'], 401)
            );
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Log the login event manually
        $this->auditService->logAuthEvent('logged_in', $user, [
            'firstname' => $user->firstname,
            'username' => $user->username,
        ]);

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ];
    }

    public function logout(User $user): void
    {
        // Log the logout event manually
        $this->auditService->logAuthEvent('logged_out', $user, [
            'firstname' => $user->firstname,
            'username' => $user->username,
        ]);

        $user->currentAccessToken()->delete();
    }
}
