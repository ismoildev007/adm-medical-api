<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;

class PermissionService
{
    public function getFilteredPermissions(array $filters): LengthAwarePaginator
    {
        $query = Permission::query();

        $search = $filters['s'] ?? $filters['search'] ?? null;
        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        $perPage = (int) ($filters['rows'] ?? 50);
        return $query->orderBy('name')->paginate($perPage)->withQueryString();
    }
}
