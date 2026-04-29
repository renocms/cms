<?php

namespace Reno\Cms\Interfaces\FieldTypes;

interface HasOptions
{
    public function options(array $options): static;

    public function getOptions(): array;
}