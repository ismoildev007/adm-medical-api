<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use App\Services\EmployeeStaffService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeStaffController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly EmployeeStaffService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->list($request->all());
        return $this->successResponse($data);
    }

    public function show(int $id): JsonResponse
    {
        $employeeStaff = $this->service->findById($id);

        if (!$employeeStaff) {
            return $this->errorResponse('Employee staff topilmadi.', 'NOT_FOUND', 404);
        }

        return $this->successResponse($employeeStaff);
    }
}
