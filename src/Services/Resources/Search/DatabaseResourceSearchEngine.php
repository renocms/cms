<?php

namespace Reno\Cms\Services\Resources\Search;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Reno\Cms\DTO\Resources\ResourceSearchCriteria;
use Reno\Cms\Helpers\TablePrefixHelper;
use Reno\Cms\Interfaces\Services\ResourceSearchEngineInterface;

class DatabaseResourceSearchEngine implements ResourceSearchEngineInterface
{
    public function makeSearchSubquery(ResourceSearchCriteria $criteria): Builder
    {
        $table = TablePrefixHelper::table('search_data');
        $driver = DB::getDriverName();

        return match ($driver) {
            'mysql', 'mariadb' => $this->makeMysqlSubquery($table, $criteria),
            'pgsql' => $this->makePgsqlSubquery($table, $criteria),
            default => throw new \RuntimeException(
                sprintf('Database search driver "%s" is not supported', $driver),
            ),
        };
    }

    private function makeMysqlSubquery(string $table, ResourceSearchCriteria $criteria): Builder
    {
        $query = DB::table($table)
            ->select('resource_id')
            ->selectRaw(
                'MATCH(search_text) AGAINST (? IN BOOLEAN MODE) as score',
                [$criteria->searchQuery],
            )
            ->whereRaw(
                'MATCH(search_text) AGAINST (? IN BOOLEAN MODE)',
                [$criteria->searchQuery],
            )
            ->orderByDesc('score')
            ->orderBy('resource_id');

        if ($criteria->contextId !== null) {
            $query->where('context_id', $criteria->contextId);
        }

        return $query;
    }

    private function makePgsqlSubquery(string $table, ResourceSearchCriteria $criteria): Builder
    {
        $query = DB::table($table)
            ->select('resource_id')
            ->selectRaw(
                "ts_rank(to_tsvector('simple', coalesce(search_text, '')), websearch_to_tsquery('simple', ?)) as score",
                [$criteria->searchQuery],
            )
            ->whereRaw(
                "to_tsvector('simple', coalesce(search_text, '')) @@ websearch_to_tsquery('simple', ?)",
                [$criteria->searchQuery],
            )
            ->orderByDesc('score')
            ->orderBy('resource_id');

        if ($criteria->contextId !== null) {
            $query->where('context_id', $criteria->contextId);
        }

        return $query;
    }
}
