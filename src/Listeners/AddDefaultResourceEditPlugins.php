<?php

namespace Reno\Cms\Listeners;

use Reno\Cms\Events\ResourceEditPluginsRegistering;

class AddDefaultResourceEditPlugins
{
    public function handle(ResourceEditPluginsRegistering $event): void
    {
        $plugins = config('cms.javascript_plugins.resource_edit', []);

        foreach ($plugins as $pluginClass) {
            if (is_string($pluginClass) && class_exists($pluginClass)) {
                $event->add(new $pluginClass());
            }
        }
    }
}
