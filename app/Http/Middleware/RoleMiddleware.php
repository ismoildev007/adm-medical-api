<?php

namespace App\Http\Middleware;

use App\Console\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RoleMiddleware
{

    // App\Http\Middleware\RoleMiddleware.php
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->hasRole('superadmin')) {
            return $next($request);
        }

        $currentRoute = Route::getCurrentRoute();
        $routeName = $currentRoute->getName();
        $permissionName = str_replace(['.', '_', ' '], '-', $routeName);

        if ($user->can($permissionName)) {
            return $next($request);
        }

        abort(403, 'Bu sahifaga kirish uchun ruxsatingiz yo\'q.');
    }
}
