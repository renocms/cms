<?php

namespace Reno\Cms\Interfaces\FieldTypes;

use Reno\Cms\Models\ResourceValue;

interface SyncsResourceValueInterface
{
    public function syncResourceValue(ResourceValue $resourceValue, mixed $value): void;

    public function deleteResourceValue(ResourceValue $resourceValue): void;
}
