<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (!auth()->user()->hasRole('superadmin')) {
            abort(403, 'Bu sahifaga kirish uchun SuperAdmin roli talab qilinadi.');
        }

        return $next($request);
    }
}
