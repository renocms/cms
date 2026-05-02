<?php

namespace Reno\Cms\Events\Resources;

use Illuminate\Foundation\Events\Dispatchable;

class ResourcesReindexing
{
    use Dispatchable;

    /**
     * @var array<int, array<int, string>>
     */
    private array $searchTextsByResourceId = [];

    /**
     * @param int[] $resourceIds
     */
    public function __construct(
        public readonly array $resourceIds,
    )
    {
    }

    public function addSearchText(int $resourceId, string $searchText): void
    {
        $searchText = trim($searchText);
        if ($searchText === '') {
            return;
        }

        if (!isset($this->searchTextsByResourceId[$resourceId])) {
            $this->searchTextsByResourceId[$resourceId] = [];
        }

        $this->searchTextsByResourceId[$resourceId][] = $searchText;
    }

    /**
     * @return string[]
     */
    public function getSearchTextsForResource(int $resourceId): array
    {
        return $this->searchTextsByResourceId[$resourceId] ?? [];
    }
}
