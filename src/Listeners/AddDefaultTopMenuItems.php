<?php

namespace Reno\Cms\Listeners;

use Reno\Cms\Events\TopMenuItemsRegistering;

class AddDefaultTopMenuItems
{
    public function handle(TopMenuItemsRegistering $event): void
    {
        $menuItems = config('cms.top_menu_items', []);

        foreach ($menuItems as $menuItemClass) {
            if (is_string($menuItemClass) && class_exists($menuItemClass)) {
                $event->add(new $menuItemClass());
            }
        }
    }
}
