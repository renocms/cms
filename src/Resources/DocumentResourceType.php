<?php

namespace Reno\Cms\Resources;

use Reno\Cms\Interfaces\Resources\ResourceTypeInterface;
use Reno\Cms\Models\Resource;

class DocumentResourceType implements ResourceTypeInterface
{
    public function getResourceClass(): string
    {
        return Resource::class;
    }

    public function getResourceRelations(): array
    {
        return [
            'resourceValues',
            'resourceValues.resourceField',
            'resourceValues.media',
        ];
    }

    public function getJsModule(): ?string
    {
        return null;
    }

    public function getName(): string
    {
        return __('cms::cms.resource_type_document_name');
    }

    public function getDescription(): ?string
    {
        return __('cms::cms.resource_type_document_description');
    }

    public function getIcon(): ?string
    {
        return null;
    }

    public function supportsResourceFields(): bool
    {
        return true;
    }
}

