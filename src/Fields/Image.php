<?php

namespace Reno\Cms\Fields;

use InvalidArgumentException;
use Reno\Cms\FieldTypes\MediaFieldType;

class Image extends Media
{
    public static function make(string $key): self
    {
        return (new self($key, new MediaFieldType()))
            ->accept('image/*');
    }

    public function accept(string $accept): static
    {
        if (!str_starts_with($accept, 'image/')) {
            throw new InvalidArgumentException('Image supports only image/* accept values.');
        }

        return parent::accept($accept);
    }
}
