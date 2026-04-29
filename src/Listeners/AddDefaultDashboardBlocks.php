<?php

namespace Reno\Cms\Listeners;

use Reno\Cms\Events\DashboardBlocksCollecting;
use Reno\Cms\Interfaces\DashboardBlockInterface;

class AddDefaultDashboardBlocks
{
    public function handle(DashboardBlocksCollecting $event): void
    {
        $blocks = config('cms.dashboard_blocks', []);

        foreach ($blocks as $blockClass) {
            if (is_string($blockClass) && class_exists($blockClass)) {
                /** @var DashboardBlockInterface $block */
                $block = resolve($blockClass);
                $event->addBlock($block);
            }
        }
    }
}

