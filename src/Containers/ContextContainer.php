<?php

namespace Reno\Cms\Containers;

use Reno\Cms\Interfaces\Contexts\ContextInterface;

class ContextContainer
{
    public function __construct(
        private int $id,
        private ContextInterface $context,
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getContext(): ContextInterface
    {
        return $this->context;
    }
}
