<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Reno\Cms\Events\Cache\NeedToFlushCmsCache;

class CacheController extends Controller
{
    public function flushCms(): JsonResponse
    {
        Event::dispatch(new NeedToFlushCmsCache());

        return response()->json([
            'message' => __('cms::cms.cache_flush_cms_success'),
        ]);
    }

    public function flushFull(): JsonResponse
    {
        Event::dispatch(new NeedToFlushCmsCache());
        Artisan::call('cache:clear');

        return response()->json([
            'message' => __('cms::cms.cache_flush_full_success'),
        ]);
    }
}
