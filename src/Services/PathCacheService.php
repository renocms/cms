<?php

namespace Reno\Cms\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\Builder;
use Reno\Cms\Interfaces\Services\PathCacheInterface;
use Reno\Cms\Models\Resource;
use Reno\Cms\Interfaces\Repositories\SettingRepositoryInterface;

class PathCacheService implements PathCacheInterface
{
    /**
     * @var array<int, array<string, int>>
     */
    private array $pathToIdCache = [];

    /**
     * @var array<int, array<int, string>>
     */
    private array $idToPathCache = [];

    public function __construct(
        private SettingRepositoryInterface $settingRepository,
    )
    {
    }

    public function get(int $contextId, string $path): ?int
    {
        $cache = $this->loadCache($contextId);
        
        return $cache[$path] ?? null;
    }

    public function getPathByResourceId(int $contextId, int $resourceId): ?string
    {
        $this->loadCache($contextId);

        return $this->idToPathCache[$contextId][$resourceId] ?? null;
    }

    public function put(int $contextId, string $path, int $resourceId): void
    {
        $cache = $this->loadCache($contextId);
        $cache[$path] = $resourceId;
        $this->saveCache($contextId, $cache);
    }

    public function forget(int $contextId, string $path): void
    {
        $cache = $this->loadCache($contextId);
        unset($cache[$path]);
        $this->saveCache($contextId, $cache);
    }

    public function clear(int $contextId): void
    {
        $filePath = $this->getCacheFilePath($contextId);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $this->invalidateMemoryCache($contextId);
    }

    public function rebuild(int $contextId): void
    {
        $this->clear($contextId);

        $homeResourceId = $this->settingRepository->getHomeResourceId($contextId);

        $resources = Resource::where('context_id', $contextId)
            ->where('status', 'published')
            ->where(function (Builder $query) {
                $query->where('is_folder', true)
                    ->orWhereNull('parent_id');
            })
            ->where('id', '!=', $homeResourceId)
            ->get();

        $cache = [];
        foreach ($resources as $resource) {
            $path = $this->calculatePath($resource);
            if ($path) {
                $cache[$path] = $resource->id;
            }
        }

        $this->saveCache($contextId, $cache);
    }

    private function loadCache(int $contextId): array
    {
        if (array_key_exists($contextId, $this->pathToIdCache)) {
            return $this->pathToIdCache[$contextId];
        }

        $filePath = $this->getCacheFilePath($contextId);

        if (!File::exists($filePath)) {
            $this->rebuild($contextId);

            if (!File::exists($filePath)) {
                $this->pathToIdCache[$contextId] = [];
                $this->idToPathCache[$contextId] = [];

                return [];
            }
        }

        try {
            $cache = require $filePath;
            $pathToId = is_array($cache) ? $cache : [];
        } catch (\Throwable $e) {
            $pathToId = [];
        }

        $this->pathToIdCache[$contextId] = $pathToId;
        $this->idToPathCache[$contextId] = array_flip($pathToId);

        return $pathToId;
    }

    private function saveCache(int $contextId, array $cache): void
    {
        $filePath = $this->getCacheFilePath($contextId);
        $directory = dirname($filePath);
        
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $content = "<?php\n\nreturn " . var_export($cache, true) . ";\n";
        File::put($filePath, $content);

        $this->invalidateMemoryCache($contextId);
    }

    private function getCacheFilePath(int $contextId): string
    {
        $basePath = storage_path(config('cms.path_cache.path', 'app/cms/paths'));
        return $basePath . '/' . $contextId . '.php';
    }

    private function calculatePath(Resource $resource): ?string
    {
        return $resource->calculatePath();
    }

    private function invalidateMemoryCache(int $contextId): void
    {
        unset($this->pathToIdCache[$contextId], $this->idToPathCache[$contextId]);
    }
}

