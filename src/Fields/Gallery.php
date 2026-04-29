<?php

namespace Reno\Cms\Fields;

use Reno\Cms\FieldTypes\GalleryFieldType;

class Gallery extends Repeater
{
    public static function make(string $key): static
    {
        return new self($key, new GalleryFieldType());
    }

    public function getConfiguration(): array
    {
        $config = parent::getConfiguration();
        $config['display'] = 'gallery';

        return $config;
    }
}
