<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\User;
use App\Repositories\AuditRepository;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Models\Audit;

class AuditService
{
    public function __construct(
        private readonly AuditRepository $auditRepository
    ) {}

    public function getFiltered(array $filters): LengthAwarePaginator
    {
        return $this->auditRepository->getFiltered($filters);
    }

    public function getProjects(): Collection
    {
        return $this->auditRepository->getDistinctProjects();
    }

    /**
     * Log a manual auth event (login / register / logout) to the audit database.
     */
    public function logAuthEvent(string $event, User $user, array $newValues = [], array $oldValues = []): Audit
    {
        return $this->auditRepository->logAuthEvent($event, $user, $newValues, $oldValues);
    }

    /**
     * Resolve the full model data for a given audit record.
     */
    public function resolveModelData(int $auditId): array
    {
        return $this->auditRepository->resolveModelData($auditId);
    }

    /**
     * Get aggregate statistics for charts.
     */
    public function getStats(): array
    {
        return $this->auditRepository->getStats();
    }
}