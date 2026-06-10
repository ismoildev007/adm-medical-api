<?php

namespace App\Http\Controllers\HRM;

use App\Http\Controllers\Controller;
use App\Services\PositionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly PositionService $service) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->list($request->all());
        return $this->successResponse($data);
    }

    public function show(int $id): JsonResponse
    {
        $position = $this->service->findById($id);

        if (!$position) {
            return $this->errorResponse('Position topilmadi.', 'NOT_FOUND', 404);
        }

        return $this->successResponse($position);
    }
}
