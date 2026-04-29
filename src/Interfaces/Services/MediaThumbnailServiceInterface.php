<?php

namespace Reno\Cms\Interfaces\Services;

use Reno\Cms\Models\Media;

interface MediaThumbnailServiceInterface
{
    public function getThumbnailUrl(Media $media, int $width = 80, int $height = 80, ?string $options = 'zc=1'): string;
}
