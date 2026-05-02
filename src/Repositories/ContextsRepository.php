<?php

namespace Reno\Cms\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Reno\Cms\Containers\ContextContainer;
use Reno\Cms\Enums\Lock;
use Reno\Cms\Events\ContextsRegistering;
use Reno\Cms\Interfaces\Contexts\ContextInterface;
use Reno\Cms\Interfaces\Repositories\ContextsRepositoryInterface;
use Reno\Cms\Models\Context;
use Reno\Cms\Services\ClassesDiscoverer;

class ContextsRepository implements ContextsRepositoryInterface
{
    /**
     * @var array<class-string<ContextInterface>, ContextInterface>|null
     */
    private static ?array $rawContextsCache = null;

    /**
     * @var array<class-string<ContextInterface>, ContextContainer>
     */
    private static array $contextsByClassCache = [];

    /**
     * @var array<string, Context>|null
     */
    private static ?array $contextModelsCache = null;

    /**
     * @var array<int, class-string<ContextInterface>>|null
     */
    private static ?array $contextClassesByIdCache = null;

    public function __construct(
        private readonly ClassesDiscoverer $classesDiscoverer,
    )
    {
    }

    /**
     * @return Collection<int, ContextContainer>
     */
    public function getAll(): Collection
    {
        $result = Collection::make();

        foreach (array_keys($this->getRawContexts()) as $className) {
            $container = $this->resolveContextByClassName($className);
            $result->put($container->getId(), $container);
        }

        return $result;
    }

    public function findById(int $id): ContextContainer
    {
        $this->initContextModelsCache();

        if (!isset(self::$contextClassesByIdCache[$id])) {
            throw new \RuntimeException("Context with ID {$id} not found.");
        }

        return $this->resolveContextByClassName(self::$contextClassesByIdCache[$id]);
    }

    public function findByClassName(string $className): ContextContainer
    {
        return $this->resolveContextByClassName($className);
    }

    public function getIdByClassName(string $className): int
    {
        return $this->resolveContextByClassName($className)->getId();
    }

    public function clearCache(): void
    {
        self::$rawContextsCache = null;
        self::$contextsByClassCache = [];
        self::$contextModelsCache = null;
        self::$contextClassesByIdCache = null;
    }

    /**
     * @return array<class-string<ContextInterface>, ContextInterface>
     */
    private function getRawContexts(): array
    {
        if (self::$rawContextsCache !== null) {
            return self::$rawContextsCache;
        }

        $lock = Cache::lock(Lock::Contexts->value, 30);

        if (!$lock->block(5)) {
            throw new \RuntimeException('Contexts are locked');
        }

        try {
            $event = new ContextsRegistering();
            Event::dispatch($event);

            if (config('cms.discover_contexts', true)) {
                $path = (string) config('cms.contexts_path');

                foreach ($this->classesDiscoverer->discover($path) as $className) {
                    if (!is_subclass_of($className, ContextInterface::class)) {
                        continue;
                    }

                    /** @var ContextInterface $instance */
                    $instance = app($className);
                    $event->addContext($instance);
                }
            }

            $contextsByClass = [];

            foreach ($event->getContexts() as $context) {
                $contextsByClass[$context::class] = $context;
            }

            ksort($contextsByClass);
            self::$rawContextsCache = $contextsByClass;
        } finally {
            $lock->release();
        }

        return self::$rawContextsCache;
    }

    private function resolveContextByClassName(string $className): ContextContainer
    {
        if (isset(self::$contextsByClassCache[$className])) {
            return self::$contextsByClassCache[$className];
        }

        $rawContexts = $this->getRawContexts();

        if (!isset($rawContexts[$className])) {
            throw new \RuntimeException("Context with class '{$className}' not found.");
        }

        $context = $rawContexts[$className];
        $model = $this->getModelForContext($context);
        $container = new ContextContainer(
            id: $model->getKey(),
            context: $context,
        );

        if (self::$contextModelsCache === null) {
            self::$contextModelsCache = [];
        }
        if (self::$contextClassesByIdCache === null) {
            self::$contextClassesByIdCache = [];
        }

        self::$contextsByClassCache[$className] = $container;
        self::$contextModelsCache[$className] = $model;
        self::$contextClassesByIdCache[$container->getId()] = $className;

        return $container;
    }

    private function initContextModelsCache(): void
    {
        if (self::$contextModelsCache !== null) {
            return;
        }

        $models = Context::query()->get();

        self::$contextModelsCache = $models->keyBy('class')->all();
        self::$contextClassesByIdCache = $models
            ->keyBy('id')
            ->map(fn (Context $context) => $context->class)
            ->toArray();
    }

    private function getModelForContext(ContextInterface $context): Context
    {
        $this->initContextModelsCache();

        $class = $context::class;

        if (isset(self::$contextModelsCache[$class])) {
            return self::$contextModelsCache[$class];
        }

        $model = Context::query()->updateOrCreate([
            'class' => $class,
        ]);

        if (self::$contextModelsCache === null) {
            self::$contextModelsCache = [];
        }
        if (self::$contextClassesByIdCache === null) {
            self::$contextClassesByIdCache = [];
        }

        self::$contextModelsCache[$class] = $model;
        self::$contextClassesByIdCache[$model->getKey()] = $class;

        return $model;
    }
}
