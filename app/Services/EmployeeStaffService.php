<?php

namespace App\Services;

use App\Models\EmployeeStaff;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeStaffService
{
    public function list(array $filters): LengthAwarePaginator
    {
        $query = EmployeeStaff::query()->with([
            'employee',
            'department.translations',
            'position.translations',
            'staff',
        ]);

        if ($empId = $filters['employee_id'] ?? null) {
            $query->where('employee_id', $empId);
        }

        if ($deptId = $filters['department_id'] ?? null) {
            $query->where('department_id', $deptId);
        }

        if ($staffId = $filters['staff_id'] ?? null) {
            $query->where('staff_id', $staffId);
        }

        if (isset($filters['main_staff'])) {
            $query->where('main_staff', filter_var($filters['main_staff'], FILTER_VALIDATE_BOOLEAN));
        }

        return $query->orderBy('id')->paginate(
            perPage: $filters['rows'] ?? 20,
            page: $filters['page'] ?? 1,
        );
    }

    public function findById(int $id): ?EmployeeStaff
    {
        return EmployeeStaff::with([
            'employee',
            'department.translations',
            'position.translations',
            'staff',
        ])->find($id);
    }
}
