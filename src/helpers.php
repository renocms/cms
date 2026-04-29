<?php

use Reno\Cms\Models\Resource;
use Reno\Cms\Services\CmsCache;

if (!function_exists('getClassFileModifiedAt')) {
    /**
     * Получить время последней модификации файла класса
     *
     * @param string $className FQCN класса
     * @return int|null Unix timestamp или null если файл недоступен
     */
    function getClassFileModifiedAt(string $className): ?int
    {
        try {
            $reflection = new \ReflectionClass($className);
            $path = $reflection->getFileName();
            if ($path === false || !file_exists($path)) {
                return null;
            }

            $mtime = filemtime($path);

            return $mtime !== false ? $mtime : null;
        } catch (\Throwable) {
            return null;
        }
    }
}

if (!function_exists('getCmsBuildManifest')) {
    /**
     * Получить manifest сборки CMS.
     *
     * @return array<string, mixed>
     */
    function getCmsBuildManifest(): array
    {
        $manifestPath = public_path('vendor/reno/cms/build/manifest.json');

        if (!file_exists($manifestPath)) {
            $manifestPath = __DIR__ . '/../public/build/manifest.json';
        }

        if (!file_exists($manifestPath)) {
            return [];
        }

        $manifestContents = file_get_contents($manifestPath);

        if ($manifestContents === false) {
            return [];
        }

        $manifest = json_decode($manifestContents, true);

        return is_array($manifest) ? $manifest : [];
    }
}

if (!function_exists('getCmsBuildEntry')) {
    /**
     * Получить запись из manifest сборки CMS.
     *
     * @param string $entry
     * @return array<string, mixed>|null
     */
    function getCmsBuildEntry(string $entry): ?array
    {
        $manifest = getCmsBuildManifest();
        $candidates = [
            $entry,
            ltrim($entry, '/'),
            preg_replace('/\.js$/', '', ltrim($entry, '/')) . '.js',
            'resources/js/' . ltrim($entry, '/'),
            'resources/js/' . preg_replace('/\.js$/', '', ltrim($entry, '/')) . '.js',
        ];

        $manifestEntry = null;

        foreach ($candidates as $candidate) {
            if (!is_string($candidate)) {
                continue;
            }

            if (isset($manifest[$candidate]) && is_array($manifest[$candidate])) {
                $manifestEntry = $manifest[$candidate];
                break;
            }
        }

        return is_array($manifestEntry) ? $manifestEntry : null;
    }
}

if (!function_exists('getCmsBuildAssetUrl')) {
    /**
     * Получить URL к entry-файлу сборки CMS.
     *
     * @param string $entry
     * @return string|null
     */
    function getCmsBuildAssetUrl(string $entry): ?string
    {
        $manifestEntry = getCmsBuildEntry($entry);

        if ($manifestEntry === null || !isset($manifestEntry['file']) || !is_string($manifestEntry['file'])) {
            return null;
        }

        return asset('vendor/reno/cms/build/' . ltrim($manifestEntry['file'], '/'));
    }
}

if (!function_exists('getCmsBuildCssUrls')) {
    /**
     * Получить CSS-файлы entry из manifest сборки CMS.
     *
     * @param string $entry
     * @return array<int, string>
     */
    function getCmsBuildCssUrls(string $entry): array
    {
        $manifestEntry = getCmsBuildEntry($entry);

        if ($manifestEntry === null) {
            return [];
        }

        $cssFiles = $manifestEntry['css'] ?? [];

        if (!is_array($cssFiles)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $cssFile): ?string => is_string($cssFile)
                ? asset('vendor/reno/cms/build/' . ltrim($cssFile, '/'))
                : null,
            $cssFiles
        )));
    }
}

if (!function_exists('getCmsModuleAssetUrl')) {
    /**
     * Получить публичный URL JS-модуля CMS по относительному пути исходника.
     *
     * @param string $relativeSourcePath
     * @return string
     */
    function getCmsModuleAssetUrl(string $relativeSourcePath): string
    {
        $normalizedPath = ltrim($relativeSourcePath, '/');
        $normalizedPath = preg_replace('/\.(vue|js)$/', '.js', $normalizedPath);

        return '/vendor/reno/cms/build/' . $normalizedPath;
    }
}

if (!function_exists('resourceRoute')) {
    function resourceRoute(?int $id, array $params = []): string
    {
        return CmsCache::remember('resource-route:' . $id, fn () => Resource::findOrFail($id)->getUrl())
            . (!empty($params) ? '?' . http_build_query($params) : '');
    }
}
