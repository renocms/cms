<?php

use Illuminate\Support\Facades\Route;
use Reno\Cms\Http\Actions\ShowTreeResourceAction;

Route::any('{path}', ShowTreeResourceAction::class)
    ->where('path', '.*')
    ->fallback();
