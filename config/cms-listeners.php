<?php

return [
    'listeners' => [
        \Reno\Cms\Events\DashboardBlocksCollecting::class => [
            \Reno\Cms\Listeners\AddDefaultDashboardBlocks::class,
        ],
        \Reno\Cms\Events\Resources\ResourceTypesRegistering::class => [
            \Reno\Cms\Listeners\AddDefaultResourceTypes::class,
        ],
        \Reno\Cms\Events\FieldTypesRegistering::class => [
            \Reno\Cms\Listeners\AddDefaultFieldTypes::class,
        ],
        \Reno\Cms\Events\Resources\ResourceEditPluginsRegistering::class => [
            \Reno\Cms\Listeners\AddDefaultResourceEditPlugins::class,
        ],
        \Reno\Cms\Events\JavascriptRoutesRegistering::class => [
            \Reno\Cms\Listeners\AddDefaultJavascriptRoutes::class,
        ],
        \Reno\Cms\Events\TopMenuItemsRegistering::class => [
            \Reno\Cms\Listeners\AddDefaultTopMenuItems::class,
        ],
        \Reno\Cms\Interfaces\Events\FlushesCmsCache::class => [
            \Reno\Cms\Listeners\FlushCmsCache::class,
        ],
        \Reno\Cms\Events\Resources\ResourceCreated::class => [
            \Reno\Cms\Listeners\ReindexSearchData::class,
        ],
        \Reno\Cms\Events\Resources\ResourceUpdated::class => [
            \Reno\Cms\Listeners\ReindexSearchData::class,
        ],
        \Reno\Cms\Events\Resources\ResourceMoved::class => [
            \Reno\Cms\Listeners\ReindexSearchData::class,
        ],
        \Reno\Cms\Events\Resources\ResourcePublishingStateChanged::class => [
            \Reno\Cms\Listeners\ReindexSearchData::class,
        ],
        \Reno\Cms\Events\Resources\ResourceDeleted::class => [
            \Reno\Cms\Listeners\DeleteSearchData::class,
        ],
    ],
];
