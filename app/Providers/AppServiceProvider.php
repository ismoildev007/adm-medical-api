<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \OwenIt\Auditing\Models\Audit::creating(function ($audit) {
            $audit->project_name = config('app.name', 'Laravel');
        });

        // ─── Authorization Gates ─────────────────────────────
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user->hasRole('superadmin')) {
                return true;
            }
        });

        // Dynamic permission check
        \Illuminate\Support\Facades\Gate::define('permission', function ($user, $permissionName) {
            return $user->hasPermission($permissionName);
        });

        // Register each permission as its own gate for convenience (e.g. @can('admin-users-index'))
        if (!app()->runningInConsole() || app()->runningUnitTests()) {
            try {
                \App\Models\Permission::all()->each(function ($permission) {
                    \Illuminate\Support\Facades\Gate::define($permission->name, function ($user) use ($permission) {
                        return $user->hasPermission($permission->name);
                    });
                });
            } catch (\Throwable) {
                // Skip if table doesn't exist yet
            }
        }
    }
}
