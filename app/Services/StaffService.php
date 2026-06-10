<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Pagination\LengthAwarePaginator;

class StaffService
{
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Staff::query()->with(['department.translations', 'position.translations']);

        if ($deptId = $filters['department_id'] ?? null) {
            $query->where('department_id', $deptId);
        }

        if ($posId = $filters['position_id'] ?? null) {
            $query->where('position_id', $posId);
        }

        if (isset($filters['is_department_director'])) {
            $query->where('is_department_director', filter_var($filters['is_department_director'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->orderBy('id')->paginate(
            perPage: $filters['rows'] ?? 20,
            page: $filters['page'] ?? 1,
        );
    }

    public function findById(int $id): ?Staff
    {
        return Staff::with(['department.translations', 'position.translations', 'employeeStaff.employee'])->find($id);
    }
}
