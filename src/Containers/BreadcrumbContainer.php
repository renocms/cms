<?php

namespace Reno\Cms\Containers;

use Reno\Cms\Interfaces\Resources\ResourceInterface;

readonly class BreadcrumbContainer
{
    public function __construct(
        private ResourceInterface $resource,
        private bool $isHome,
        private bool $isCurrent,
    )
    {
    }

    public function getResource(): ResourceInterface
    {
        return $this->resource;
    }

    public function isHome(): bool
    {
        return $this->isHome;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function getTitle(): ?string
    {
        return $this->resource->getTitle();
    }

    public function getUrl(): ?string
    {
        return $this->resource->getUrl();
    }
}
