<?php

namespace Reno\Cms\Repositories;

use Illuminate\Support\Collection;
use Reno\Cms\Models\ResourceField;
use Illuminate\Support\Facades\Event;
use Reno\Cms\Interfaces\Repositories\FieldTypeRepositoryInterface;
use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;
use Reno\Cms\Events\FieldTypesRegistering;

class FieldTypeRepository implements FieldTypeRepositoryInterface
{
    private static ?Collection $cache = null;

    private static ?array $fieldsCache = null;

    /**
     * @return Collection<FieldTypeInterface>
     */
    public function getAll(): Collection
    {
        if (self::$cache === null) {
            $event = new FieldTypesRegistering();
            Event::dispatch($event);

            self::$cache = Collection::make($event->getFieldTypes())
                ->keyBy(fn (FieldTypeInterface $type) => $type->getType());
        }

        return self::$cache;
    }

    public function findByType(string $type): ?FieldTypeInterface
    {
        return $this->getAll()->get($type);
    }

    public function findByFieldId(int $fieldId): ?FieldTypeInterface
    {
        if (self::$fieldsCache === null) {
            self::$fieldsCache = ResourceField::all()->pluck('type', 'id')->toArray();
        }

        if (!isset(self::$fieldsCache[$fieldId])) {
            return null;
        }

        return $this->getAll()->get(self::$fieldsCache[$fieldId]);
    }

    public function clearCache(): void
    {
        self::$cache = null;
    }
}

