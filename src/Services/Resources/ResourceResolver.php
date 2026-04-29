<?php

namespace Reno\Cms\Services\Resources;

use Reno\Cms\Interfaces\Repositories\SettingRepositoryInterface;
use Reno\Cms\Interfaces\Services\PathCacheInterface;
use Reno\Cms\Interfaces\Services\ResourceResolverInterface;
use Reno\Cms\Models\Resource;

class ResourceResolver implements ResourceResolverInterface
{
    public function __construct(
        private readonly PathCacheInterface $pathCacheService,
        private readonly SettingRepositoryInterface $settingRepository,
    )
    {
    }

    public function resolveResourceIdByPath(int $contextId, string $path): ?int
    {
        $normalizedPath = $this->normalizePath($path);

        if ($normalizedPath === '/') {
            return $this->settingRepository->getHomeResourceId($contextId);
        }

        $resourceId = $this->pathCacheService->get($contextId, $normalizedPath);
        if (is_int($resourceId) && $resourceId > 0) {
            return $resourceId;
        }

        [$folderResourceId, $remainingSegments] = $this->findNearestFolderResourceId($normalizedPath, $contextId);
        if ($folderResourceId === null || $remainingSegments === []) {
            return null;
        }

        $currentParentId = $folderResourceId;

        foreach ($remainingSegments as $segment) {
            $childResource = Resource::query()
                ->where('context_id', $contextId)
                ->where('parent_id', $currentParentId)
                ->where('slug', $segment)
                ->where('status', 'published')
                ->first();

            if (!$childResource instanceof Resource) {
                return null;
            }

            $currentParentId = $childResource->id;
        }

        return $currentParentId;
    }

    /**
     * @return array{0: int|null, 1: array<int, string>}
     */
    private function findNearestFolderResourceId(string $normalizedPath, int $contextId): array
    {
        $segments = explode('/', trim($normalizedPath, '/'));
        $remainingSegments = [];

        while ($segments !== []) {
            $candidatePath = '/' . implode('/', $segments);
            $candidateResourceId = $this->pathCacheService->get($contextId, $candidatePath);

            if (is_int($candidateResourceId) && $candidateResourceId > 0) {
                $candidateResource = Resource::query()->find($candidateResourceId);
                if ($candidateResource instanceof Resource && $candidateResource->is_folder) {
                    return [$candidateResourceId, array_reverse($remainingSegments)];
                }
            }

            $remainingSegments[] = (string) array_pop($segments);
        }

        return [null, []];
    }

    private function normalizePath(string $path): string
    {
        $normalizedPath = '/' . trim($path, '/');

        return $normalizedPath === '/' ? $normalizedPath : rtrim($normalizedPath, '/');
    }
}
