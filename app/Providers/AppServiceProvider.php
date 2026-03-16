<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use OwenIt\Auditing\Models\Audit;

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
        Audit::creating(function ($audit) {
            $audit->project_name = config('app.name', 'Laravel');
        });

        // ─── Authorization Gates ─────────────────────────────
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('superadmin')) {
                return true;
            }
        });

        // Dynamic permission check
        Gate::define('permission', function ($user, $permissionName) {
            return $user->hasPermission($permissionName);
        });

        if (!app()->runningInConsole() || app()->runningUnitTests()) {
            try {
                Permission::all()->each(function ($permission) {
                    Gate::define($permission->name, function ($user) use ($permission) {
                        return $user->hasPermission($permission->name);
                    });
                });
            } catch (\Throwable) {
                // Skip if table doesn't exist yet
            }
        }
    }
}
