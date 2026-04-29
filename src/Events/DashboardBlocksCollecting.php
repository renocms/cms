<?php

namespace Reno\Cms\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Reno\Cms\Interfaces\DashboardBlockInterface;

class DashboardBlocksCollecting
{
    use Dispatchable, SerializesModels;

    /**
     * @var array<DashboardBlockInterface>
     */
    protected array $blocks = [];

    public function addBlock(DashboardBlockInterface $block): void
    {
        $this->blocks[] = $block;
    }

    /**
     * @return array<DashboardBlockInterface>
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }
}

