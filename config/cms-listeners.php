<?php

return [
    'listeners' => [
        \Reno\Cms\Events\DashboardBlocksCollecting::class => [
            \Reno\Cms\Listeners\AddDefaultDashboardBlocks::class,
        ],
        \Reno\Cms\Events\ResourceTypesRegistering::class => [
            \Reno\Cms\Listeners\AddDefaultResourceTypes::class,
        ],
        \Reno\Cms\Events\FieldTypesRegistering::class => [
            \Reno\Cms\Listeners\AddDefaultFieldTypes::class,
        ],
        \Reno\Cms\Events\ResourceEditPluginsRegistering::class => [
            \Reno\Cms\Listeners\AddDefaultResourceEditPlugins::class,
        ],
        \Reno\Cms\Events\JavascriptRoutesRegistering::class => [
            \Reno\Cms\Listeners\AddDefaultJavascriptRoutes::class,
        ],
        \Reno\Cms\Events\TopMenuItemsRegistering::class => [
            \Reno\Cms\Listeners\AddDefaultTopMenuItems::class,
        ],
    ],
];
