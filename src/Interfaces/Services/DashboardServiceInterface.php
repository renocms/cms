<?php

namespace Reno\Cms\Interfaces\Services;

use Reno\Cms\Interfaces\DashboardBlockInterface;

interface DashboardServiceInterface
{
    /**
     * Получить блоки для dashboard
     *
     * @return array<int, DashboardBlockInterface>
     */
    public function getBlocks(): array;
}

