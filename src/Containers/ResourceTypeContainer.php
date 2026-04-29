<?php

namespace Reno\Cms\Containers;

use Reno\Cms\Interfaces\Resources\ResourceTypeInterface;

readonly class ResourceTypeContainer
{
    public function __construct(
        private int $id,
        private ResourceTypeInterface $resourceType,
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getResourceType(): ResourceTypeInterface
    {
        return $this->resourceType;
    }
}
