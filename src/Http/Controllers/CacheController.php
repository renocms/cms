<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function flushCms(): JsonResponse
    {
        Cache::store('cms')->flush();

        return response()->json([
            'message' => __('cms::cms.cache_flush_cms_success'),
        ]);
    }

    public function flushFull(): JsonResponse
    {
        Cache::store('cms')->flush();
        Artisan::call('cache:clear');

        return response()->json([
            'message' => __('cms::cms.cache_flush_full_success'),
        ]);
    }
}
