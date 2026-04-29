<?php

namespace Reno\Cms\Interfaces\Contexts;

use Illuminate\Http\Request;

interface ContextInterface
{
    public function getKey(): string;

    public function getLocale(): string;

    public function modifyRequest(Request $request): void;

    public function getLabel(): string;
}
