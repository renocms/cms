<?php

namespace Reno\Cms\Interfaces\Services;

use Reno\Cms\Models\Resource;
use Illuminate\Support\Collection;

interface ResourcesBreadcrumbsBuilderInterface
{
    public function build(Resource $resource): Collection;
}
