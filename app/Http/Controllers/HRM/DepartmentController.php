<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use App\Services\DepartmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly DepartmentService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->list($request->all());
        return $this->successResponse($data);
    }

    public function show(int $id): JsonResponse
    {
        $department = $this->service->findById($id);

        if (!$department) {
            return $this->errorResponse('Department topilmadi.', 'NOT_FOUND', 404);
        }

        return $this->successResponse($department);
    }
}
