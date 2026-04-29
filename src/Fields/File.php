<?php

namespace Reno\Cms\Fields;

use Reno\Cms\Fields\Concerns\ValidatesFile;
use Reno\Cms\FieldTypes\FileFieldType;

class File extends AbstractField
{
    use ValidatesFile;

    public static function make(string $key): self
    {
        return new self($key, new FileFieldType());
    }
}
