<?php

namespace Reno\Cms\Services;

use Illuminate\Support\Facades\Event;
use Reno\Cms\Events\DashboardBlocksCollecting;
use Reno\Cms\Interfaces\DashboardBlockInterface;
use Reno\Cms\Interfaces\Services\DashboardServiceInterface;

class DashboardService implements DashboardServiceInterface
{
    /**
     * @return array<int, DashboardBlockInterface>
     */
    public function getBlocks(): array
    {
        $event = new DashboardBlocksCollecting();
        Event::dispatch($event);

        $blocks = $event->getBlocks();

        // Сортируем блоки по порядку сортировки
        usort($blocks, function (DashboardBlockInterface $a, DashboardBlockInterface $b) {
            return $a->getSortOrder() <=> $b->getSortOrder();
        });

        return $blocks;
    }
}

