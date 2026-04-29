<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Reno\Cms\Events\JavascriptRoutesRegistering;
use Reno\Cms\Events\TopMenuItemsRegistering;
use Reno\Cms\Http\Resources\Dashboard\DashboardBlockResource;
use Reno\Cms\Http\Resources\Menu\TopMenuItemResource;
use Reno\Cms\Http\Resources\Routes\JavascriptRouteResource;
use Reno\Cms\Interfaces\Services\DashboardServiceInterface;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardServiceInterface $dashboardService
    )
    {
    }

    public function index(): JsonResponse
    {
        $blocks = $this->dashboardService->getBlocks();

        return DashboardBlockResource::collection($blocks)->response();
    }

    public function getJavascriptRoutes(): JsonResponse
    {
        $event = new JavascriptRoutesRegistering();
        Event::dispatch($event);

        $routes = $event->getAll();

        return JavascriptRouteResource::collection($routes)->response();
    }

    public function getTopMenuItems(): JsonResponse
    {
        $event = new TopMenuItemsRegistering();
        Event::dispatch($event);

        $menuItems = $event->getAll();

        return TopMenuItemResource::collection($menuItems)->response();
    }
}

