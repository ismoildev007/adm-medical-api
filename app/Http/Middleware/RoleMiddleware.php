<?php

namespace App\Http\Middleware;

use App\Console\Helpers\PermissionHelper;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
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

        return response()->json(['message' => 'Bu amalni bajarish uchun ruxsatingiz yo\'q.'], 403);
    }
}