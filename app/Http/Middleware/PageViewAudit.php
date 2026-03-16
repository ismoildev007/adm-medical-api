<?php

namespace App\Http\Middleware;

use Closure;
use OwenIt\Auditing\Models\Audit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class PageViewAudit
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (Auth::check() && $request->isMethod('get') && $response->getStatusCode() == 200) {
            Audit::create([
                'user_type'   => get_class(Auth::user()),
                'user_id'     => Auth::id(),
                'event'       => 'viewed',
                'auditable_type' => 'Page',
                'auditable_id'   => 0,
                'old_values'  => [],
                'new_values'  => ['url' => $request->fullUrl()],
                'url'         => $request->fullUrl(),
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
                'tags'        => 'page_view',
            ]);
        }

        return $response;
    }
}
