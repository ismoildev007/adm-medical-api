<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Models\Audit;

class AuditRepository
{
    /**
     * Manually log an auth event (login / register / logout) to the global audits DB.
     */
    public function logAuthEvent(string $event, User $user, array $newValues = [], array $oldValues = []): ?Audit
    {
        if (!config('audit.enabled')) {
            return null;
        }

        return Audit::create([
            'project_name'   => config('app.name'),
            'user_type'      => User::class,
            'user_id'        => $user->id,
            'event'          => $event,
            'auditable_type' => User::class,
            'auditable_id'   => $user->id,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'url'            => Request::fullUrl(),
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
            'tags'           => 'auth',
        ]);
    }

}

