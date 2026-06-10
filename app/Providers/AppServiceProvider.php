<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use LdapRecord\Connection;
use OwenIt\Auditing\Models\Audit;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Connection::class, function () {
            return new Connection([
                'hosts'    => explode(',', config('ldap.ldap_hosts', '')),
                'base_dn'  => config('ldap.ldap_base_dn'),
                'username' => config('ldap.ldap_username'),
                'password' => config('ldap.ldap_password'),
                'port'     => (int) config('ldap.ldap_port', 3268),
            ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Audit::creating(function (Audit $audit) {
            $audit->project_name = config('app.name');

            $connection = config('audit.drivers.database.connection');
            if ($connection) {
                $audit->setConnection($connection);
            }
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
                Cache::remember('all_permissions', 3600, fn() => Permission::all())
                    ->each(function ($permission) {
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
