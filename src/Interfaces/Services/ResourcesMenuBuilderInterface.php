<?php

namespace Reno\Cms\Interfaces\Services;

use Illuminate\Support\Collection;
use Reno\Cms\DTO\Resources\ResourceTreeBuilderParams;

interface ResourcesMenuBuilderInterface
{
    public function build(ResourceTreeBuilderParams $params): Collection;
}
