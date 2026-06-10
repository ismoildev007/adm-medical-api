<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly EmployeeService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->list($request->all());
        return $this->successResponse($data);
    }

    public function show(int $id): JsonResponse
    {
        $employee = $this->service->findById($id);

        if (!$employee) {
            return $this->errorResponse('Xodim topilmadi.', 'NOT_FOUND', 404);
        }

        return $this->successResponse($employee);
    }
}
