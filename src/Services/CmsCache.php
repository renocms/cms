<?php

namespace Reno\Cms\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class CmsCache
{
    public static function remember(string $key, callable $callback): mixed
    {
        return Cache::store('cms')->remember($key, config('cms.cache.ttl', now()->addMinutes(60)), $callback);
    }

    public static function rememberForPage(string $key, callable $callback, ?array $params = []): mixed
    {
        if ($params === null) {
            $params = $_GET;
        } else {
            $params = Arr::only($_GET, $params);
        }

        $params['contextId'] = app('cms.current_context_id');

        return self::remember($key . ':' . request()->path() . ':' . http_build_query($params), $callback);
    }

    public static function flush(): void
    {
        Cache::store('cms')->flush();
    }
}
