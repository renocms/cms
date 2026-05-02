<?php

return [
    'bindings' => [
        \Reno\Cms\Interfaces\Services\ResourceServiceInterface::class => \Reno\Cms\Services\Resources\ResourceService::class,
        \Reno\Cms\Interfaces\Contexts\ContextResolverInterface::class => \Reno\Cms\Services\Contexts\DefaultContextResolver::class,
        \Reno\Cms\Interfaces\Services\RoleServiceInterface::class => \Reno\Cms\Services\Users\RoleService::class,
        \Reno\Cms\Interfaces\Services\UserServiceInterface::class => \Reno\Cms\Services\Users\UserService::class,
        \Reno\Cms\Interfaces\Services\PermissionServiceInterface::class => \Reno\Cms\Services\Users\PermissionService::class,
        \Reno\Cms\Interfaces\Services\ResourceVersionServiceInterface::class => \Reno\Cms\Services\Resources\ResourceVersionService::class,
        \Reno\Cms\Interfaces\Services\DashboardServiceInterface::class => \Reno\Cms\Services\DashboardService::class,
        \Reno\Cms\Interfaces\Services\MediaServiceInterface::class => \Reno\Cms\Services\MediaService::class,
        \Reno\Cms\Interfaces\Services\MediaThumbnailServiceInterface::class => \Reno\Cms\Services\MediaThumbnailService::class,
        \Reno\Cms\Interfaces\Services\ResourcesTreeBuilderInterface::class => \Reno\Cms\Services\Resources\ResourcesTreeBuilder::class,
        \Reno\Cms\Interfaces\Services\ResourcesBreadcrumbsBuilderInterface::class => \Reno\Cms\Services\Resources\ResourcesBreadcrumbsBuilder::class,
        \Reno\Cms\Interfaces\Services\ResourcesMenuBuilderInterface::class => \Reno\Cms\Services\Resources\ResourcesMenuBuilder::class,
        \Reno\Cms\Interfaces\Services\ResourceResolverInterface::class => \Reno\Cms\Services\Resources\ResourceResolver::class,
        \Reno\Cms\Interfaces\Services\ResourceSearchEngineInterface::class => \Reno\Cms\Services\Resources\Search\SearchEngineManager::class,
        \Reno\Cms\Interfaces\Services\ResourceSearchIndexerInterface::class => \Reno\Cms\Services\Resources\Search\SearchIndexerManager::class,
    ],
    'singletons' => [
        \Reno\Cms\Interfaces\Services\PathCacheInterface::class => \Reno\Cms\Services\PathCacheService::class,
        \Reno\Cms\Interfaces\Repositories\ContextsRepositoryInterface::class => \Reno\Cms\Repositories\ContextsRepository::class,
        \Reno\Cms\Interfaces\Repositories\ResourceRepositoryInterface::class => \Reno\Cms\Repositories\ResourceRepository::class,
        \Reno\Cms\Interfaces\Repositories\ResourceTypeRepositoryInterface::class => \Reno\Cms\Repositories\ResourceTypeRepository::class,
        \Reno\Cms\Interfaces\Repositories\FieldTypeRepositoryInterface::class => \Reno\Cms\Repositories\FieldTypeRepository::class,
        \Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface::class => \Reno\Cms\Repositories\ResourceLayoutRepository::class,
        \Reno\Cms\Interfaces\Repositories\SettingRepositoryInterface::class => \Reno\Cms\Repositories\SettingRepository::class,
    ],
];
