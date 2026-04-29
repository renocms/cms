<?php

namespace Reno\Cms\Repositories;

use Reno\Cms\Enums\Lock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Reno\Cms\Events\ResourceTypesRegistering;
use Reno\Cms\Containers\ResourceTypeContainer;
use Reno\Cms\Interfaces\Repositories\ResourceTypeRepositoryInterface;
use Reno\Cms\Interfaces\Resources\ResourceTypeInterface;
use Reno\Cms\Models\ResourceType;

class ResourceTypeRepository implements ResourceTypeRepositoryInterface
{
    /**
     * @var array<class-string<ResourceTypeInterface>, ResourceTypeInterface>|null
     */
    private static ?array $rawResourceTypesCache = null;

    /**
     * @var array<class-string<ResourceTypeInterface>, ResourceTypeContainer>
     */
    private static array $resourceTypesByClassCache = [];

    /**
     * @var array<string, ResourceType>|null
     */
    private static ?array $resourceTypeModelsCache = null;

    /**
     * @var array<int, class-string<ResourceTypeInterface>>|null
     */
    private static ?array $resourceTypeClassesByIdCache = null;

    /**
     * @return Collection<ResourceTypeContainer>
     */
    public function getAll(): Collection
    {
        $result = Collection::make();

        foreach (array_keys($this->getRawResourceTypes()) as $className) {
            $container = $this->resolveResourceTypeByClassName($className);
            $result->put($container->getId(), $container);
        }

        return $result;
    }

    /**
     * @throws \RuntimeException Если тип ресурса не найден
     */
    public function findById(int $id): ResourceTypeContainer
    {
        $this->initResourceTypeModelsCache();

        if (!isset(self::$resourceTypeClassesByIdCache[$id])) {
            throw new \RuntimeException("Resource type with ID {$id} not found.");
        }

        return $this->resolveResourceTypeByClassName(self::$resourceTypeClassesByIdCache[$id]);
    }

    public function findByClassname(string $classname): ResourceTypeContainer
    {
        return $this->resolveResourceTypeByClassName($classname);
    }

    /**
     * @throws \RuntimeException Если тип ресурса не найден
     */
    public function getIdByClassName(string $className): int
    {
        return $this->resolveResourceTypeByClassName($className)->getId();
    }

    public function clearCache(): void
    {
        self::$rawResourceTypesCache = null;
        self::$resourceTypesByClassCache = [];
        self::$resourceTypeModelsCache = null;
        self::$resourceTypeClassesByIdCache = null;
    }

    /**
     * @return array<class-string<ResourceTypeInterface>, ResourceTypeInterface>
     */
    private function getRawResourceTypes(): array
    {
        if (self::$rawResourceTypesCache !== null) {
            return self::$rawResourceTypesCache;
        }

        $lock = Cache::lock(Lock::ResourceTypes->value);

        if (!$lock->block(5)) {
            throw new \RuntimeException('Resource types are locked');
        }

        try {
            $event = new ResourceTypesRegistering();
            Event::dispatch($event);

            $resourceTypesByClass = [];

            foreach ($event->getResourceTypes() as $resourceType) {
                $resourceTypesByClass[$resourceType::class] = $resourceType;
            }

            ksort($resourceTypesByClass);
            self::$rawResourceTypesCache = $resourceTypesByClass;
        } finally {
            $lock->release();
        }

        return self::$rawResourceTypesCache;
    }

    private function resolveResourceTypeByClassName(string $className): ResourceTypeContainer
    {
        if (isset(self::$resourceTypesByClassCache[$className])) {
            return self::$resourceTypesByClassCache[$className];
        }

        $rawResourceTypes = $this->getRawResourceTypes();

        if (!isset($rawResourceTypes[$className])) {
            throw new \RuntimeException("Resource type with class '{$className}' not found.");
        }

        $resourceType = $rawResourceTypes[$className];
        $model = $this->getModelForResourceType($resourceType);

        $container = new ResourceTypeContainer(
            id: $model->getKey(),
            resourceType: $resourceType,
        );

        if (self::$resourceTypeModelsCache === null) {
            self::$resourceTypeModelsCache = [];
        }
        if (self::$resourceTypeClassesByIdCache === null) {
            self::$resourceTypeClassesByIdCache = [];
        }

        self::$resourceTypesByClassCache[$className] = $container;
        self::$resourceTypeModelsCache[$className] = $model;
        self::$resourceTypeClassesByIdCache[$container->getId()] = $className;

        return $container;
    }

    private function initResourceTypeModelsCache(): void
    {
        if (self::$resourceTypeModelsCache !== null) {
            return;
        }

        $models = ResourceType::query()->get();

        self::$resourceTypeModelsCache = $models->keyBy('class')->all();
        self::$resourceTypeClassesByIdCache = $models
            ->keyBy('id')
            ->map(fn (ResourceType $resourceType) => $resourceType->class)
            ->toArray();
    }

    private function getModelForResourceType(ResourceTypeInterface $resourceType): ResourceType
    {
        $this->initResourceTypeModelsCache();

        if (isset(self::$resourceTypeModelsCache[$resourceType::class])) {
            return self::$resourceTypeModelsCache[$resourceType::class];
        }

        $model = ResourceType::query()->updateOrCreate([
            'class' => $resourceType::class,
        ]);

        if (self::$resourceTypeModelsCache === null) {
            self::$resourceTypeModelsCache = [];
        }
        if (self::$resourceTypeClassesByIdCache === null) {
            self::$resourceTypeClassesByIdCache = [];
        }

        self::$resourceTypeModelsCache[$resourceType::class] = $model;
        self::$resourceTypeClassesByIdCache[$model->getKey()] = $resourceType::class;

        return $model;
    }
}

