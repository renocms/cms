<?php

namespace Reno\Cms\Interfaces\Forms;

interface HasSchema
{
    public function schema(array $schema): static;

    public function getSchema(): array;
}
