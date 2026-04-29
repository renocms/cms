<?php

namespace Reno\Cms\Containers;

use Illuminate\Support\Collection;
use Reno\Cms\Interfaces\Resources\ResourceInterface;

readonly class MenuItemContainer
{
    public function __construct(
        private ResourceInterface $resource,
        private bool $isCurrent,
        private bool $isActive,
        /** @var Collection<static> $children */
        private Collection $children,
    )
    {
    }

    public function getResource(): ResourceInterface
    {
        return $this->resource;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function hasChildren(): bool
    {
        return $this->children->isNotEmpty();
    }

    /** @return Collection<static> */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getUrl(): ?string
    {
        return $this->resource->getUrl();
    }

    public function getTitle(): ?string
    {
        return $this->resource->getTitle();
    }
}
