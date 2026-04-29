<?php

namespace Reno\Cms\DTO\Resources;

class ResourceValueForEdit
{
    public function __construct(
        public readonly int $resourceFieldId,
        public readonly mixed $value,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            resourceFieldId: (int) ($data['resource_field_id'] ?? 0),
            value: $data['value'] ?? null,
        );
    }
}
