<?php

namespace App\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Request;
use OwenIt\Auditing\Models\Audit;

class AuditRepository
{
    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = Audit::latest();
        $user = auth()->user();

        // Enforce project-based permissions
        if ($user && !$user->hasRole('superadmin')) {
            $allowedProjects = is_array($user->project_permission) ? $user->project_permission : [];
            $query->whereIn('project_name', $allowedProjects);
        }

        if (!empty($filters['project'])) {
            $query->where('project_name', $filters['project']);
        }

        if (!empty($filters['events']) && is_array($filters['events'])) {
            $query->whereIn('event', $filters['events']);
        }

        if (!empty($filters['event'])) {
            $query->where('event', $filters['event']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('auditable_type', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('old_values', 'like', "%{$search}%")
                  ->orWhere('new_values', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('auditable_id', 'like', "%{$search}%");
            });
        }

        return $query->paginate(50);
    }

    public function getDistinctProjects(): Collection
    {
        $query = Audit::select('project_name')
            ->distinct()
            ->whereNotNull('project_name');

        $user = auth()->user();
        if ($user && !$user->hasRole('superadmin')) {
            $allowedProjects = is_array($user->project_permission) ? $user->project_permission : [];
            $query->whereIn('project_name', $allowedProjects);
        }

        return $query->orderBy('project_name')
            ->pluck('project_name');
    }

    /**
     * Manually log an auth event (login / register / logout) to the global audits DB.
     */
    public function logAuthEvent(string $event, User $user, array $newValues = [], array $oldValues = []): Audit
    {
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

    /**
     * Resolve the full data of the audited model.
     * - If the model class exists locally: query the DB and return all columns.
     * - Otherwise: reconstruct from audit's old_values + new_values (best-effort).
     */
    public function resolveModelData(int $auditId): array
    {
        $audit = Audit::findOrFail($auditId);

        $modelClass  = $audit->auditable_type;
        $modelId     = $audit->auditable_id;
        $modelName   = class_basename($modelClass);

        if (class_exists($modelClass)) {
            try {
                $record = $modelClass::find($modelId);
                if ($record) {
                    return [
                        'source'     => 'live',
                        'model'      => $modelName,
                        'id'         => $modelId,
                        'project'    => $audit->project_name,
                        'data'       => $record->toArray(),
                    ];
                }
            } catch (\Throwable) {
                // Fall through to audit snapshot
            }
        }

        $old = is_array($audit->old_values) ? $audit->old_values : [];
        $new = is_array($audit->new_values) ? $audit->new_values : [];
        $snapshot = array_merge(['id' => $modelId], $old, $new);

        return [
            'source'  => 'audit_snapshot',
            'model'   => $modelName,
            'id'      => $modelId,
            'project' => $audit->project_name,
            'note'    => 'Model class not found locally. Showing audit snapshot (merged old+new values).',
            'data'    => $snapshot,
        ];
    }

    /**
     * Get aggregate statistics for charts.
     */
    public function getStats(): array
    {
        $user = auth()->user();
        $allowedProjects = ($user && !$user->hasRole('superadmin')) 
            ? (is_array($user->project_permission) ? $user->project_permission : []) 
            : null;

        $baseQuery = Audit::query();
        if ($allowedProjects !== null) {
            $baseQuery->whereIn('project_name', $allowedProjects);
        }

        // Projects distribution
        $projects = (clone $baseQuery)->selectRaw('project_name, count(*) as count')
            ->groupBy('project_name')
            ->orderByDesc('count')
            ->get();

        // Events distribution
        $events = (clone $baseQuery)->selectRaw('event, count(*) as count')
            ->groupBy('event')
            ->orderByDesc('count')
            ->get();

        // Top URLs
        $urls = (clone $baseQuery)->selectRaw('url, count(*) as count')
            ->whereNotNull('url')
            ->groupBy('url')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Activity Trend (Last 30 days)
        $days = (clone $baseQuery)->selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'projects' => $projects,
            'events'   => $events,
            'urls'     => $urls,
            'timeline' => $days,
        ];
    }
}

