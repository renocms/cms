<?php

namespace Reno\Cms\Interfaces\Layouts;

use Reno\Cms\Models\Resource;
use Illuminate\Contracts\View\View;

interface LayoutViewComposerInterface
{
    public function compose(View $view, Resource $resource): void;
}
