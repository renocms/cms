<?php

namespace Reno\Cms\Containers;

use Reno\Cms\Interfaces\Forms\FieldInterface;

readonly class FieldContainer
{
    public function __construct(
        private int $id,
        private FieldInterface $field,
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getField(): FieldInterface
    {
        return $this->field;
    }
}
