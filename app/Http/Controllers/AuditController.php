<?php

namespace App\Http\Controllers;

use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function __construct(
        private readonly AuditService $auditService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $audits = $this->auditService->getFiltered($request->only([
            'project', 'event', 'date_from', 'date_to', 'search', 'events'
        ]));

        return response()->json($audits);
    }

    public function projects(): JsonResponse
    {
        return response()->json($this->auditService->getProjects());
    }

    public function modelData(int $auditId): JsonResponse
    {
        $data = $this->auditService->resolveModelData($auditId);

        return response()->json($data);
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->auditService->getStats());
    }
}
