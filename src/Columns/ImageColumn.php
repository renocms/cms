<?php

namespace Reno\Cms\Columns;

use Reno\Cms\Interfaces\Resources\ResourceInterface;
use Reno\Cms\Models\Media;

class ImageColumn extends AbstractColumn
{
    public function getType(): string
    {
        return 'image';
    }

    public function resolveMedia(ResourceInterface $resource): ?Media
    {
        return $this->getResourceValue($resource)?->media;
    }
}
