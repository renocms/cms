<?php

namespace Reno\Cms\FieldTypes;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

class GalleryFieldType extends RepeaterFieldType implements FieldTypeInterface
{
    public function getType(): string
    {
        return 'gallery';
    }

    public function getName(): string
    {
        return __('cms::cms.field_type_gallery_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.field_type_gallery_description');
    }
}
