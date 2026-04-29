<?php

namespace Reno\Cms\Plugins\Routes;

use Reno\Cms\Interfaces\JavascriptRouteInterface;

class SettingsRoute implements JavascriptRouteInterface
{
    public function getName(): string
    {
        return 'settings';
    }

    public function getPath(): string
    {
        return 'settings';
    }

    public function getJsModule(): string
    {
        return getCmsModuleAssetUrl('components/settings/Settings.vue');
    }

    public function getMeta(): array
    {
        return [];
    }
}
