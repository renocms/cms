<?php

namespace Reno\Cms\Listeners;

use Reno\Cms\Events\Resources\ResourceTypesRegistering;
use Reno\Cms\Resources\DocumentResourceType;

class AddDefaultResourceTypes
{
    public function handle(ResourceTypesRegistering $event): void
    {
        $event->addResourceType(new DocumentResourceType());
    }
}

