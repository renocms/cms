<?php

namespace Reno\Cms\Interfaces\Resources;

use Illuminate\Database\Eloquent\Builder;

interface ResourcesCatalogInterface
{
    public function getLabel(): string;

    public function getCatalogSchema(): array;

    public function modifyQueryUsing(Builder $query): void;
}
