<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Permission;
use OwenIt\Auditing\Models\Audit;

class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {}

    public function getAll(): Collection
    {
        return $this->userRepository->all();
    }

    public function getOne(User $user): User
    {
        return $this->userRepository->find($user);
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        return $this->userRepository->create($data);
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        return $this->userRepository->update($user, $data);
    }

    public function getFilteredUsers(array $filters): Collection
    {
        $query = User::with('roles');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['role'])) {
            $query->whereHas('roles', function($q) use ($filters) {
                $q->where('roles.name', $filters['role']);
            });
        }

        if (!empty($filters['permission'])) {
            $query->whereHas('roles.permissions', function($q) use ($filters) {
                $q->where('permissions.name', $filters['permission']);
            });
        }

        return $query->get();
    }

    public function getUserFormData(): array
    {
        return [
            'roles' => Role::orderBy('name')->get(),
            'permissions' => Permission::orderBy('name')->get(),
            'allProjects' => Audit::select('project_name')
                ->distinct()
                ->whereNotNull('project_name')
                ->orderBy('project_name')
                ->pluck('project_name'),
        ];
    }

    public function createUser(array $data): User
    {
        $user = User::create([
            'firstname' => $data['firstname'],
            'lastname'  => $data['lastname'],
            'username'  => $data['username'],
            'password'  => Hash::make($data['password']),
            'project_permission' => $data['projects'] ?? [],
            'created_by' => auth()->id(),
        ]);

        if (!empty($data['roles'])) {
            $user->roles()->attach($data['roles']);
        }

        return $user;
    }

    public function updateRolesAndProjects(User $user, array $data): void
    {
        if (isset($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }
        $user->update(['project_permission' => $data['projects'] ?? []]);
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
