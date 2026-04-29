<?php

namespace Reno\Cms\Interfaces\Resources;

use Illuminate\Database\Eloquent\Collection;
use Reno\Cms\Containers\ResourceLayoutContainer;

interface ResourceInterface
{
    public function getId(): int;

    public function getTitle(): ?string;

    public function getUrl(): ?string;

    public function getResourceLayout(): ResourceLayoutContainer;

    public function getUltimateParentId(int $level): ?int;

    public function getParents(): Collection;

    public function getParentIds(): array;

    public function hasValue(string $field): bool;

    public function getValue(string $field): mixed;

    public function calculatePath(): ?string;
}
