<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use LdapRecord\Connection;

class AuthService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly AuditService $auditService,
    ) {
    }

    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'username' => $data['username'],
            'password' => $data['password'],
            'project_permission' => $data['projects'] ?? [],
            'created_by' => null,
        ]);

        if (!empty($data['roles'])) {
            $user->roles()->attach($data['roles']);
        }

        $token = $user->createToken('auth_token')->accessToken;

        $this->auditService->logAuthEvent('registered', $user, [
            'firstname' => $user->firstname,
            'username' => $user->username,
        ]);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }

    public function login(string $username, string $password): array
    {
        $localOnlyUsers = config('ldap.local_only_users', []);
        $useLdap = strtolower(config('ldap.auth_with')) === 'ldap'
            && !in_array(strtolower($username), $localOnlyUsers);

        $user = $useLdap
            ? $this->authenticateViaLdap($username, $password)
            : $this->authenticateViaPassword($username, $password);

        $token = $user->createToken('auth_token')->accessToken;

        $this->auditService->logAuthEvent('logged_in', $user, [
            'firstname' => $user->firstname,
            'username' => $user->username,
        ]);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ];
    }

    public function logout(User $user): void
    {
        $this->auditService->logAuthEvent('logged_out', $user, [
            'firstname' => $user->firstname,
            'username' => $user->username,
        ]);

        $user->tokens()->update(['revoked' => true]);
    }
    private function authenticateViaLdap(string $username, string $password): User
    {
        $connection = app(Connection::class);

        $ldapUser = $connection
            ->query()
            ->search()
            ->findBy('sAMAccountName', strtolower($username));

        if (!$ldapUser) {
            abort(401, 'user_not_found');
        }

        $upn = Arr::get($ldapUser, 'userprincipalname.0');

        if (!$connection->auth()->attempt($upn, $password)) {
            abort(401, 'password_incorrect');
        }

        $localUser = $this->userRepository->findByUserName(strtoupper($username));

        if (!$localUser) {
            abort(403, 'Tizimdan foydalanish huquqiga ega emassiz.');
        }

        $localUser->update(['password' => bcrypt($password)]);

        return $localUser->load('roles.permissions');
    }

    private function authenticateViaPassword(string $username, string $password): User
    {
        $user = $this->userRepository->findByUserName($username);

        if (!$user || !Hash::check($password, $user->password)) {
            abort(401, 'The provided credentials are incorrect.');
        }

        return $user->load('roles.permissions');
    }
}
