<?php

namespace Reno\Cms\Columns;

use Reno\Cms\Interfaces\Resources\ResourceInterface;

class TextColumn extends AbstractColumn
{
    public function getType(): string
    {
        return 'text';
    }

    public function resolveValue(ResourceInterface $resource): mixed
    {
        $value = $this->getAttributeValue($resource);

        if ($value === null) {
            $value = $this->getResourceValue($resource)?->value;
        }

        return $this->formatValue($value);
    }
}
