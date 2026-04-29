<?php

namespace Reno\Cms\Helpers;

class TablePrefixHelper
{
    public static function getPrefix(): string
    {
        return config('cms.table_prefix', 'cms_');
    }

    public static function table(string $name): string
    {
        return static::getPrefix() . $name;
    }
}

