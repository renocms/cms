<?php

namespace Reno\Cms\Interfaces\Repositories;

use Illuminate\Support\Collection;
use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;

interface FieldTypeRepositoryInterface
{
    public function getAll(): Collection;

    public function findByType(string $type): ?FieldTypeInterface;

    public function findByFieldId(int $fieldId): ?FieldTypeInterface;

    public function clearCache(): void;
}

