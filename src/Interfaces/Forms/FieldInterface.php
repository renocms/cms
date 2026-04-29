<?php

namespace Reno\Cms\Interfaces\Forms;

use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

interface FieldInterface extends FormElementInterface
{
    public function getKey(): string;

    public function getFieldType(): FieldTypeInterface;

    public function getConfiguration(): array;

    public function getValidationRules(): array;

    public function isRequired(): bool;
}
