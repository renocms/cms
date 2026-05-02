<?php

namespace Reno\Cms\Services\Resources\Search;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Reno\Cms\Events\Resources\ResourcesReindexing;
use Reno\Cms\Interfaces\Services\ResourceSearchIndexerInterface;
use Reno\Cms\Models\Resource;
use Reno\Cms\Models\ResourceValue;
use Reno\Cms\Models\SearchData;

class DatabaseResourceSearchIndexer implements ResourceSearchIndexerInterface
{
    public function reindexAll(): int
    {
        SearchData::query()->truncate();

        $indexed = 0;

        Resource::query()
            ->select('id')
            ->orderBy('id')
            ->chunkById(200, function ($resources) use (&$indexed): void {
                $resourceIds = $resources->pluck('id')->all();
                $this->reindexResources($resourceIds);
                $indexed += count($resourceIds);
            });

        return $indexed;
    }

    public function reindexResource(int $resourceId): void
    {
        $this->reindexResources([$resourceId]);
    }

    public function reindexResources(array $resourceIds): void
    {
        $resourceIds = array_values(array_unique(array_map('intval', $resourceIds)));
        if ($resourceIds === []) {
            return;
        }

        $resources = Resource::query()
            ->whereIn('id', $resourceIds)
            ->with(['resourceValues.resourceField'])
            ->get();

        $existingIds = $resources->pluck('id')->map(static fn (mixed $id) => (int) $id)->all();
        $missingIds = array_values(array_diff($resourceIds, $existingIds));
        if ($missingIds !== []) {
            SearchData::query()->whereIn('resource_id', $missingIds)->delete();
        }

        $event = new ResourcesReindexing($existingIds);
        Event::dispatch($event);

        $timestamp = now();
        $rows = [];

        foreach ($resources as $resource) {
            $rows[] = [
                'context_id' => (int) $resource->context_id,
                'resource_id' => (int) $resource->id,
                'search_text' => $this->makeSearchText(
                    $resource,
                    $event->getSearchTextsForResource((int) $resource->id),
                ),
                'updated_at' => $timestamp,
                'created_at' => $timestamp,
            ];
        }

        if ($rows === []) {
            return;
        }

        DB::table(SearchData::getTableName())->upsert(
            $rows,
            ['resource_id'],
            ['context_id', 'search_text', 'updated_at'],
        );
    }

    public function deleteResource(int $resourceId): void
    {
        SearchData::query()
            ->where('resource_id', $resourceId)
            ->delete();
    }

    private function makeSearchText(Resource $resource, array $extraSearchTexts = []): string
    {
        $parts = [
            (string) $resource->slug,
        ];

        foreach ($resource->resourceValues as $resourceValue) {
            if (!$resourceValue instanceof ResourceValue) {
                continue;
            }

            $fieldKey = (string) ($resourceValue->resourceField?->key ?? '');
            $value = trim((string) ($resourceValue->value ?? ''));
            if ($value === '') {
                continue;
            }

            $parts[] = $fieldKey;
            $parts[] = strip_tags($value);
        }

        foreach ($extraSearchTexts as $extraSearchText) {
            $normalizedExtraSearchText = trim($extraSearchText);
            if ($normalizedExtraSearchText === '') {
                continue;
            }

            $parts[] = strip_tags($normalizedExtraSearchText);
        }

        return trim(implode(' ', $parts));
    }
}
