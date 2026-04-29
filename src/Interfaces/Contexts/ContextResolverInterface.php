<?php

namespace Reno\Cms\Interfaces\Contexts;

use Illuminate\Http\Request;
use Reno\Cms\Containers\ContextContainer;

interface ContextResolverInterface
{
    public function resolve(Request $request): ContextContainer;
}
