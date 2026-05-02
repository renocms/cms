<?php

return [
    'search' => [
        'driver' => env('CMS_SEARCH_DRIVER', 'database'),
        
        'drivers' => [
            'database' => [
                'engine_class' => \Reno\Cms\Services\Resources\Search\DatabaseResourceSearchEngine::class,
                'indexer_class' => \Reno\Cms\Services\Resources\Search\DatabaseResourceSearchIndexer::class,
                'max_hits' => (int) env('CMS_SEARCH_MAX_HITS', 200),
                'page_size_default' => (int) env('CMS_SEARCH_PAGE_SIZE_DEFAULT', 20),
                'page_size_max' => (int) env('CMS_SEARCH_PAGE_SIZE_MAX', 100),
            ],
        ],
    ],
];
