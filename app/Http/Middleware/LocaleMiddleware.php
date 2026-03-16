<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->segment(1);
        $languages = ['uz', 'ru', 'en'];

        if (in_array($locale, $languages)) {
            App::setLocale($locale);
            URL::defaults(['locale' => $locale]);

            if ($request->route()) {
                $request->route()->forgetParameter('locale');
            }
        } else {
            App::setLocale('uz');
            URL::defaults(['locale' => 'uz']);
        }

        return $next($request);
    }
}
