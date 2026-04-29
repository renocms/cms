<?php

namespace Reno\Cms\Layouts;

use Reno\Cms\Resources\DocumentResourceType;
use Reno\Cms\Interfaces\Layouts\ResourceLayoutInterface;

abstract class BaseLayout implements ResourceLayoutInterface
{
    public function getResourceType(): string
    {
        return DocumentResourceType::class;
    }

    public function allowChildren(): bool
    {
        return true;
    }

    public function getChildrenLayouts(): array
    {
        return [];
    }

    public function getAttachedEntity(): ?string
    {
        return null;
    }

    public function getResourceCatalog(): ?string
    {
        return null;
    }

    public function getViewName(): ?string
    {
        return null;
    }

    public function getViewComposer(): ?string
    {
        return null;
    }
}
