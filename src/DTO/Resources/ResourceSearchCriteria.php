<?php

namespace Reno\Cms\DTO\Resources;

class ResourceSearchCriteria
{
    public function __construct(
        public readonly string $searchQuery,
        public readonly ?int $contextId,
    )
    {
    }
}
