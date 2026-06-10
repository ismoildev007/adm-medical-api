<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StoreMedicineRequest;
use App\Http\Requests\Warehouse\UpdateMedicineRequest;
use App\Services\MedicineService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly MedicineService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->successResponse($this->service->list($request->all()));
    }

    public function show(int $id): JsonResponse
    {
        $medicine = $this->service->findById($id);

        if (!$medicine) {
            return $this->errorResponse('Dori topilmadi.', 'NOT_FOUND', 404);
        }

        return $this->successResponse($medicine);
    }

    public function store(StoreMedicineRequest $request): JsonResponse
    {
        return $this->successResponse($this->service->store($request->validated()), 'Dori yaratildi.', 201);
    }

    public function update(UpdateMedicineRequest $request, int $id): JsonResponse
    {
        $medicine = $this->service->findById($id);

        if (!$medicine) {
            return $this->errorResponse('Dori topilmadi.', 'NOT_FOUND', 404);
        }

        return $this->successResponse($this->service->update($medicine, $request->validated()), 'Dori yangilandi.');
    }

    public function destroy(int $id): JsonResponse
    {
        $medicine = $this->service->findById($id);

        if (!$medicine) {
            return $this->errorResponse('Dori topilmadi.', 'NOT_FOUND', 404);
        }

        $this->service->delete($medicine);
        return $this->successResponse(null, 'Dori o\'chirildi.');
    }
}