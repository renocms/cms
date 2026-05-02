<?php

namespace Reno\Cms\Interfaces\Services;

use Illuminate\Database\Query\Builder;
use Reno\Cms\DTO\Resources\ResourceSearchCriteria;

interface ResourceSearchEngineInterface
{
    public function makeSearchSubquery(ResourceSearchCriteria $criteria): Builder;
}
