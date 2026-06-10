<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use App\Services\StaffService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly StaffService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->list($request->all());
        return $this->successResponse($data);
    }

    public function show(int $id): JsonResponse
    {
        $staff = $this->service->findById($id);

        if (!$staff) {
            return $this->errorResponse('Staff topilmadi.', 'NOT_FOUND', 404);
        }

        return $this->successResponse($staff);
    }
}
