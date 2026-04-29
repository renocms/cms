<?php

namespace Reno\Cms\Containers;

use Reno\Cms\Models\ResourceValue;

readonly class ResourceValueContainer
{
    public function __construct(
        private string $name,
        private mixed $value,
        private FieldContainer $fieldContainer,
        private ?ResourceValue $model = null,
    )
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getFieldContainer(): FieldContainer
    {
        return $this->fieldContainer;
    }

    public function getModel(): ?ResourceValue
    {
        return $this->model;
    }
}
