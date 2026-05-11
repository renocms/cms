<?php

return [
    'dashboard_blocks' => [
        \Reno\Cms\Dashboard\WelcomeBlock::class,
        \Reno\Cms\Dashboard\RecentResourceChangesBlock::class,
        \Reno\Cms\Dashboard\NewUsersBlock::class,
    ],
    'javascript_plugins' => [
        'resource_edit' => [
            \Reno\Cms\Plugins\SlugGeneratorPlugin::class,
        ],
    ],
    'javascript_routes' => [
        \Reno\Cms\Plugins\Routes\DashboardRoute::class,
        \Reno\Cms\Plugins\Routes\UsersRoute::class,
        \Reno\Cms\Plugins\Routes\UserEditRoute::class,
        \Reno\Cms\Plugins\Routes\RolesRoute::class,
        \Reno\Cms\Plugins\Routes\RoleEditRoute::class,
        \Reno\Cms\Plugins\Routes\PermissionsRoute::class,
        \Reno\Cms\Plugins\Routes\SettingsRoute::class,
        \Reno\Cms\Plugins\Routes\ResourceCatalogRoute::class,
        \Reno\Cms\Plugins\Routes\ResourceEditRoute::class,
    ],
    'top_menu_items' => [
        \Reno\Cms\Plugins\Menu\UsersMenuContainer::class,
        \Reno\Cms\Plugins\Menu\SettingsMenuContainer::class,
        \Reno\Cms\Plugins\Menu\UsersMenuItem::class,
        \Reno\Cms\Plugins\Menu\RolesMenuItem::class,
        \Reno\Cms\Plugins\Menu\SettingsMenuItem::class,
    ],
];
