<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\AuditRepository;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Models\Audit;

class AuditService
{
    public function __construct(
        private readonly AuditRepository $auditRepository
    ) {}
    public function logAuthEvent(string $event, User $user, array $newValues = [], array $oldValues = []): ?Audit
    {
        return $this->auditRepository->logAuthEvent($event, $user, $newValues, $oldValues);
    }

}
