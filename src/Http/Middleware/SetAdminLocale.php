<?php

namespace Reno\Cms\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) config('cms.admin_locale', config('cms.default_locale', 'en'));
        app()->setLocale($locale);

        return $next($request);
    }
}
