<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StoreMedicineTransactionRequest;
use App\Services\MedicineTransactionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MedicineTransactionController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly MedicineTransactionService $service) {}

    public function index(Request $request): JsonResponse
    {
        return $this->successResponse($this->service->list($request->all()));
    }

    public function show(int $id): JsonResponse
    {
        $transaction = $this->service->findById($id);

        if (!$transaction) {
            return $this->errorResponse('Tranzaksiya topilmadi.', 'NOT_FOUND', 404);
        }

        return $this->successResponse($transaction);
    }

    public function store(StoreMedicineTransactionRequest $request): JsonResponse
    {
        $transaction = $this->service->store(
            $request->validated(),
            $request->user()->id
        );

        return $this->successResponse(
            $transaction->load(['medicine', 'createdBy']),
            'Tranzaksiya amalga oshirildi.',
            201
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $transaction = $this->service->findById($id);

        if (!$transaction) {
            return $this->errorResponse('Tranzaksiya topilmadi.', 'NOT_FOUND', 404);
        }

        $this->service->delete($transaction);
        return $this->successResponse(null, 'Tranzaksiya bekor qilindi.');
    }
}