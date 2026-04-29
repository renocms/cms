<?php

use Illuminate\Support\Facades\Route;
use Reno\Cms\Http\Controllers\AuthController;
use Reno\Cms\Http\Controllers\DashboardController;
use Reno\Cms\Http\Controllers\ResourceController;
use Reno\Cms\Http\Controllers\ContextController;
use Reno\Cms\Http\Controllers\UserController;
use Reno\Cms\Http\Controllers\RoleController;
use Reno\Cms\Http\Controllers\PermissionController;
use Reno\Cms\Http\Controllers\SettingController;
use Reno\Cms\Http\Controllers\ResourceLayoutController;
use Reno\Cms\Http\Controllers\MediaController;
use Reno\Cms\Http\Controllers\ResourceCatalogController;
use Reno\Cms\Http\Controllers\CacheController;

// Авторизация
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/javascript-routes', [DashboardController::class, 'getJavascriptRoutes']);
    Route::get('/top-menu-items', [DashboardController::class, 'getTopMenuItems']);

    // Contexts
    Route::get('/contexts', [ContextController::class, 'index']);

    // Resources
    Route::get('/resources', [ResourceController::class, 'index']);
    Route::get('/resources/catalog', [ResourceCatalogController::class, 'show']);
    Route::get('/resource/catalog/resources', [ResourceCatalogController::class, 'resources']);
    Route::get('/resources/make-draft', [ResourceController::class, 'makeDraft']);
    Route::get('/resources/plugins', [ResourceController::class, 'getPlugins']);
    Route::post('/resources', [ResourceController::class, 'store']);
    Route::get('/resources/{id}', [ResourceController::class, 'show']);
    Route::get('/resources/{id}/children', [ResourceController::class, 'children']);
    Route::put('/resources/{id}', [ResourceController::class, 'update']);
    Route::post('/resources/{id}/move', [ResourceController::class, 'move']);
    Route::delete('/resources/{id}', [ResourceController::class, 'destroy'])->middleware('cms.permission:resources.delete');

    // Resource Layouts
    Route::get('/resource-layouts', [ResourceLayoutController::class, 'index']);

    // Users
    Route::get('/users', [UserController::class, 'index'])->middleware('cms.permission:users.view');
    Route::get('/users/{id}', [UserController::class, 'show'])->middleware('cms.permission:users.view');
    Route::post('/users', [UserController::class, 'store'])->middleware('cms.permission:users.create');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('cms.permission:users.edit');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('cms.permission:users.delete');

    // Roles
    Route::get('/roles', [RoleController::class, 'index'])->middleware('cms.permission:roles.view');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->middleware('cms.permission:roles.view');
    Route::post('/roles', [RoleController::class, 'store'])->middleware('cms.permission:roles.create');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->middleware('cms.permission:roles.edit');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->middleware('cms.permission:roles.delete');

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index'])->middleware('cms.permission:permissions.view');
    Route::get('/permissions/{id}', [PermissionController::class, 'show'])->middleware('cms.permission:permissions.view');

    // Cache (сброс кэша CMS и приложения)
    Route::post('/cache/cms', [CacheController::class, 'flushCms'])->middleware('cms.permission:settings.manage');
    Route::post('/cache/full', [CacheController::class, 'flushFull'])->middleware('cms.permission:settings.manage');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->middleware('cms.permission:settings.manage');
    Route::put('/settings', [SettingController::class, 'updateMany'])->middleware('cms.permission:settings.manage');
    Route::delete('/settings/{id}', [SettingController::class, 'destroy'])->middleware('cms.permission:settings.manage');

    // Media
    Route::get('/media', [MediaController::class, 'index'])->middleware('cms.permission:media.view');
    Route::post('/media/thumbnails', [MediaController::class, 'thumbnails'])->middleware('cms.permission:media.view');
    Route::get('/media/{id}', [MediaController::class, 'show'])->middleware('cms.permission:media.view');
    Route::post('/media', [MediaController::class, 'store'])->middleware('cms.permission:media.create');
    Route::put('/media/{id}', [MediaController::class, 'update'])->middleware('cms.permission:media.edit');
    Route::delete('/media/{id}', [MediaController::class, 'destroy'])->middleware('cms.permission:media.delete');
});
