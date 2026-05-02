<?php

namespace Reno\Cms\Helpers;

use RuntimeException;

class TablePrefixHelper
{
    public static function getPrefix(): string
    {
        $prefix = trim((string) config('cms.table_prefix'));

        if ($prefix === '') {
            throw new RuntimeException('CMS table prefix is not configured. Please run `php artisan cms:install`.');
        }

        return $prefix;
    }

    public static function table(string $name): string
    {
        return static::getPrefix() . $name;
    }
}

