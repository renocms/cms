<?php

namespace Reno\Cms\Containers;

use Reno\Cms\Interfaces\Resources\ResourcesCatalogInterface;

class ResourcesCatalogContainer
{
    public function __construct(
        private readonly int $id,
        private readonly ResourcesCatalogInterface $catalog,
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCatalog(): ResourcesCatalogInterface
    {
        return $this->catalog;
    }
}
