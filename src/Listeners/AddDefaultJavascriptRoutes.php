<?php

namespace Reno\Cms\Listeners;

use Reno\Cms\Events\JavascriptRoutesRegistering;

class AddDefaultJavascriptRoutes
{
    public function handle(JavascriptRoutesRegistering $event): void
    {
        $routes = config('cms.javascript_routes', []);

        foreach ($routes as $routeClass) {
            if (is_string($routeClass) && class_exists($routeClass)) {
                $event->add(new $routeClass());
            }
        }
    }
}
