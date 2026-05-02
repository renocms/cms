<?php

namespace Reno\Cms;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Reno\Cms\Events\AdminApiRoutesRegistering;
use Reno\Cms\Events\AdminRoutesRegistering;
use Reno\Cms\Http\Middleware\CheckPermission;
use Reno\Cms\Http\Middleware\ContextMiddleware;
use Reno\Cms\Http\Middleware\SetAdminLocale;

class RenoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/cms.php',
            'cms'
        );

        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app->make('config');
        $config->set('cms.cache', array_merge(
            [
                'file_path' => env('CMS_CACHE_FILE_PATH', storage_path('framework/cache/cms-data')),
                'ttl' => env('CMS_CACHE_TTL', 3600),
            ],
            $config->get('cms.cache', [])
        ));

        $config->set('cache.stores.cms', [
            'driver' => 'file',
            'path' => $config->get('cms.cache.file_path'),
            'lock_path' => $config->get('cms.cache.file_path'),
        ]);

        foreach (config('cms.bindings', []) as $interface => $implementation) {
            $this->app->bind($interface, $implementation);
        }

        foreach (config('cms.singletons', []) as $interface => $implementation) {
            $this->app->singleton($interface, $implementation);
        }
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/cms.php' => config_path('cms.php'),
            __DIR__ . '/../config/cms-bindings.php' => config_path('cms-bindings.php'),
            __DIR__ . '/../config/cms-listeners.php' => config_path('cms-listeners.php'),
            __DIR__ . '/../config/cms-search.php' => config_path('cms-search.php'),
            __DIR__ . '/../config/cms-front.php' => config_path('cms-front.php'),
        ], 'cms-config');

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'cms');
        
        // Загрузка языковых файлов
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'cms');

        // Публикация собранных ассетов админки
        $this->publishes([
            __DIR__ . '/../public/build' => public_path('vendor/reno/cms/build'),
            __DIR__ . '/../resources/css/vendor/quill/quill.snow.css' => public_path('vendor/reno/cms/quill/quill.snow.css'),
            __DIR__ . '/../resources/js/vendor/quill/quill.js' => public_path('vendor/reno/cms/quill/quill.js'),
        ], 'cms-assets');

        // Загрузка миграций
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Регистрация middleware
        $this->registerMiddleware();

        // Регистрация событий
        $this->registerEvents();

        // Регистрация команд
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Reno\Cms\Console\Commands\GenerateTranslations::class,
                \Reno\Cms\Console\Commands\InstallCms::class,
                \Reno\Cms\Console\Commands\ReindexSearchData::class,
            ]);
        }

        $this->loadRoutes();
    }

    protected function registerMiddleware(): void
    {
        $this->app['router']->aliasMiddleware('cms.permission', CheckPermission::class);
        $this->app['router']->aliasMiddleware('cms.admin_locale', SetAdminLocale::class);
        $this->app['router']->aliasMiddleware('cms.context', ContextMiddleware::class);
    }

    protected function registerEvents(): void
    {
        $listeners = config('cms.listeners', []);

        foreach ($listeners as $eventClass => $eventListeners) {
            if (!is_array($eventListeners)) {
                $eventListeners = [$eventListeners];
            }

            foreach ($eventListeners as $listener) {
                if (is_string($listener) || is_callable($listener) || is_array($listener)) {
                    Event::listen($eventClass, $listener);
                }
            }
        }
    }

    protected function loadRoutes(): void
    {
        $prefix = config('cms.admin_prefix', 'admin');
        $router = $this->app['router'];

        Route::prefix($prefix)
            ->middleware(['web', 'cms.admin_locale'])
            ->group(function () use ($router) {
                Route::middleware('auth')->group(function () use ($router) {
                    event(new AdminRoutesRegistering($router));
                });

                Route::prefix('api')->group(function () use ($router) {
                    Route::middleware('auth')->group(function () use ($router) {
                        event(new AdminApiRoutesRegistering($router));
                    });
                    
                    $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
                });

                Route::get('{any?}', function () {
                    return view('cms::admin');
                })->where('any', '^(?!api).*');
            });

        Route::middleware(['web', 'cms.context'])->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }
}

