<?php

return array_merge(
    [
        'admin_prefix' => env('CMS_ADMIN_PREFIX', 'admin'),
        'table_prefix' => env('CMS_TABLE_PREFIX', 'cms_'),
        'middleware' => ['web', 'auth'],
        'admin_locale' => env('CMS_ADMIN_LOCALE', env('CMS_DEFAULT_LOCALE', 'ru')),
        'default_locale' => env('CMS_DEFAULT_LOCALE', 'ru'),
        'available_locales' => ['en', 'ru'],
        'media' => [
            'disk' => env('CMS_MEDIA_DISK', 'public'),
            'path' => env('CMS_MEDIA_PATH', 'cms/media'),
            'max_size' => env('CMS_MEDIA_MAX_SIZE', 10240),
            'allowed_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'],
        ],
        'cache' => [
            'file_path' => env('CMS_CACHE_FILE_PATH', storage_path('framework/cache/cms-data')),
            'ttl' => env('CMS_CACHE_TTL', 3600),
        ],
        'path_cache' => [
            'path' => env('CMS_PATH_CACHE_PATH', 'app/cms/paths'),
        ],
        'default_layout_class' => env('CMS_DEFAULT_LAYOUT_CLASS'),
        'contexts_path' => base_path(env('CMS_CONTEXTS_PATH', 'app/Reno/Contexts')),
        'discover_contexts' => env('CMS_DISCOVER_CONTEXTS', true),
        'layouts_path' => base_path(env('CMS_LAYOUTS_PATH', 'app/Reno/Layouts')),
        'discover_layouts' => env('CMS_DISCOVER_LAYOUTS', true),
    ],
    require __DIR__ . '/cms-bindings.php',
    require __DIR__ . '/cms-listeners.php',
    require __DIR__ . '/cms-search.php',
    require __DIR__ . '/cms-front.php'
);
