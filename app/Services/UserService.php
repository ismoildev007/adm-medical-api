<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Collection; // still used by getAll()
use Illuminate\Support\Arr;
use LdapRecord\Connection;
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
        return $this->userRepository->create($data);
    }

    public function update(User $user, array $data): User
    {
        return $this->userRepository->update($user, $data);
    }

    public function getFilteredUsers(array $filters): array
    {
        $query = User::with('roles');

        if (!empty($filters['s'])) {
            $search = $filters['s'];
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['roles'])) {
            $query->whereHas('roles', function ($q) use ($filters) {
                $q->where('roles.name', $filters['roles']);
            });
        }

        if (!empty($filters['permissions'])) {
            $query->whereHas('roles.permissions', function ($q) use ($filters) {
                $q->where('permissions.name', $filters['permissions']);
            });
        }

        $perPage = (int) ($filters['rows'] ?? 20);
        $page    = (int) ($filters['page'] ?? 1);

        $paginated = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'from'         => $paginated->firstItem(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'to'           => $paginated->lastItem(),
                'total'        => $paginated->total(),
            ],
        ];
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
        $existing = User::withTrashed()->where('username', $data['username'])->first();

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->update([
                'firstname'          => $data['firstname'],
                'lastname'           => $data['lastname'],
                'password'           => $data['password'] ?? $existing->password,
                'project_permission' => $data['projects'] ?? [],
                'created_by'         => auth()->id(),
                'deleted_by'         => null,
            ]);
            $existing->roles()->detach();
            if (!empty($data['roles'])) {
                $existing->roles()->attach($data['roles']);
            }
            return $existing->fresh();
        }

        $user = User::create([
            'firstname'          => $data['firstname'],
            'lastname'           => $data['lastname'],
            'username'           => $data['username'],
            'password'           => $data['password'] ?? null,
            'project_permission' => $data['projects'] ?? [],
            'created_by'         => auth()->id(),
        ]);

        if (!empty($data['roles'])) {
            $user->roles()->attach($data['roles']);
        }

        return $user;
    }

    public function updateUserDetails(User $user, array $data): void
    {
        if ($user->username === 'superadmin') {
            if (!empty($data['password'])) {
                $user->update(['password' => $data['password']]);
            }
            return;
        }

        if (isset($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }
        
        $updateData = [
            'firstname' => $data['firstname'] ?? $user->firstname,
            'lastname'  => $data['lastname'] ?? $user->lastname,
            'username'  => $data['username'] ?? $user->username,
            'project_permission' => $data['projects'] ?? [],
        ];

        if (!empty($data['password'])) {
            $updateData['password'] = $data['password'];
        }

        $user->update($updateData);
    }

    public function delete(User $user): void
    {
        if ($user->username === 'superadmin') {
            abort(403, 'Superadmin o\'chirilishi mumkin emas.');
        }
        $user->update(['deleted_by' => auth()->id()]);
        $user->delete();
    }

    // LDAP dan foydalanuvchi ma'lumotlarini qidiradi (admin panel uchun)
    // Yangi local user yaratishdan oldin HRM dagi ma'lumotlarni olish uchun ishlatiladi
    public function findFromLdap(string $username): array|null
    {
        $connection = app(Connection::class);

        $ldapUser = $connection
            ->query()
            ->search()
            ->findBy('sAMAccountName', strtolower($username));

        if (!$ldapUser) {
            return null;
        }

        return [
            'username'  => Arr::get($ldapUser, 'samaccountname.0'),
            'firstname' => Arr::get($ldapUser, 'givenname.0'),
            'lastname'  => Arr::get($ldapUser, 'sn.0'),
            'cn'        => Arr::get($ldapUser, 'cn.0'),
            'name'      => Arr::get($ldapUser, 'name.0'),
        ];
    }
}
