<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentService
{
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Department::query()
            ->with(['translations'])
            ->withCount('staff');

        if ($search = $filters['s'] ?? null) {
            $query->whereHas('translations', fn($q) => $q->where('name', 'ilike', "%{$search}%"));
        }

        if ($parentId = $filters['parent_id'] ?? null) {
            $query->where('parent_id', $parentId);
        }

        if ($typeId = $filters['department_type_id'] ?? null) {
            $query->where('department_type_id', $typeId);
        }

        return $query->orderBy('sequence')->paginate(
            perPage: $filters['rows'] ?? 20,
            page: $filters['page'] ?? 1,
        );
    }

    public function findById(int $id): ?Department
    {
        return Department::with(['translations', 'staff.position'])->find($id);
    }
}
