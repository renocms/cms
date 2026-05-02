<?php

namespace Reno\Cms\Console\Commands;

use Illuminate\Console\Command;
use Reno\Cms\Interfaces\Services\ResourceSearchIndexerInterface;

class ReindexSearchData extends Command
{
    protected $signature = 'cms:search:reindex';

    protected $description = 'Rebuild full-text search index for CMS resources';

    public function handle(ResourceSearchIndexerInterface $resourceSearchIndexer): int
    {
        $this->info('Reindexing search data...');

        $indexed = $resourceSearchIndexer->reindexAll();

        $this->info(sprintf('Search data was reindexed. Indexed resources: %d', $indexed));

        return self::SUCCESS;
    }
}
