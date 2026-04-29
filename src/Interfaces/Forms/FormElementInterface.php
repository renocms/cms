<?php

namespace Reno\Cms\Interfaces\Forms;

interface FormElementInterface
{
    public function getName(): string;

    public function getDescription(): ?string;
}
