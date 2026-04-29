<?php

namespace Reno\Cms\DTO\Resources;

class ResourceTreeBuilderParams
{
    /**
     * @param array<int, class-string>|null $onlyLayouts
     * @param array<int, class-string>|null $exceptLayouts
     * @param ValueFilter[]|null $valueFilters
     * @param string[]|null $onlyFields
     * @param Sort[]|null $sortBy
     */
    public function __construct(
        public ?int $contextId = null,
        public ?int $parentId = null,
        public int $depth = 4,
        public bool $onlyPublished = true,
        public bool $onlyForMenu = true,
        public bool $showCatalogChildren = true,
        public ?array $onlyLayouts = null,
        public ?array $exceptLayouts = null,
        public ?array $valueFilters = null,
        public ?array $onlyFields = null,
        public ?array $sortBy = null,
        public ?int $limit = null,  // TODO
        public ?\Closure $modifyQueryUsing = null,
        public ?\Closure $modifyFoldersQueryUsing = null,
    )
    {
    }
}
