<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;

class EmployeeService
{
    public function list(array $filters): LengthAwarePaginator
    {
        $query = Employee::query();

        if ($search = $filters['s'] ?? null) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ilike', "%{$search}%")
                  ->orWhere('last_name', 'ilike', "%{$search}%")
                  ->orWhere('middle_name', 'ilike', "%{$search}%")
                  ->orWhere('tabel', 'ilike', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if ($deptId = $filters['department_id'] ?? null) {
            $query->whereHas('employeeStaff', fn($q) => $q->where('department_id', $deptId));
        }

        return $query->orderBy('last_name')->paginate(
            perPage: $filters['rows'] ?? 20,
            page: $filters['page'] ?? 1,
        );
    }

    public function findById(int $id): ?Employee
    {
        return Employee::with([
            'employeeStaff.department.translations',
            'employeeStaff.position.translations',
            'employeeStaff.staff',
        ])->find($id);
    }
}
