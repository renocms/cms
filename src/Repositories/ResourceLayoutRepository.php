<?php

namespace Reno\Cms\Repositories;

use Carbon\Carbon;
use Reno\Cms\Enums\Lock;
use Reno\Cms\FormElements\Tab;
use Illuminate\Support\Collection;
use Reno\Cms\Models\ResourceField;
use Reno\Cms\Models\ResourceLayout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Cache;
use Reno\Cms\Containers\FieldContainer;
use Reno\Cms\Interfaces\Forms\FieldInterface;
use Reno\Cms\Events\ResourceLayoutResolved;
use Reno\Cms\Events\ResourceLayoutsRegistering;
use Reno\Cms\Containers\ResourceLayoutContainer;
use Reno\Cms\Interfaces\Forms\FormElementInterface;
use Reno\Cms\Interfaces\Layouts\ResourceLayoutInterface;
use Reno\Cms\Interfaces\Repositories\ResourceTypeRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface;
use Reno\Cms\Services\ClassesDiscoverer;

class ResourceLayoutRepository implements ResourceLayoutRepositoryInterface
{
    /**
     * @var array<class-string<ResourceLayoutInterface>, ResourceLayoutInterface>|null
     */
    private static ?array $rawLayoutsCache = null;

    /**
     * @var array<class-string<ResourceLayoutInterface>, ResourceLayoutContainer>
     */
    private static array $layoutsByClassCache = [];

    /**
     * @var array<int, class-string<ResourceLayoutInterface>>|null
     */
    private static ?array $layoutsClassesByIdCache = null;

    /**
     * @var array<string, ResourceLayout>|null
     */
    private static ?array $layoutModelsCache = null;

    /**
     * @var Collection<string, ResourceField>|null
     */
    private static ?array $resourceFieldsCache = null;

    public function __construct(
        protected ResourceTypeRepositoryInterface $resourceTypeRepository,
        private readonly ClassesDiscoverer $classesDiscoverer,
    )
    {
    }

    private function getMainTabName(): string
    {
        return __('cms::cms.main_tab_name');
    }

    public function getAll(): Collection
    {
        $result = Collection::make();

        foreach (array_keys($this->getRawLayouts()) as $className) {
            $layoutContainer = $this->resolveLayoutByClassName($className);
            $result->put($layoutContainer->getId(), $layoutContainer);
        }

        return $result;
    }

    public function findById(int $id): ResourceLayoutContainer
    {
        $this->initLayoutModelsCache();

        if (!isset(self::$layoutsClassesByIdCache[$id])) {
            throw new \RuntimeException("Layout with ID {$id} not found.");
        }

        return $this->resolveLayoutByClassName(self::$layoutsClassesByIdCache[$id]);
    }

    public function findByClassname(string $classname): ResourceLayoutContainer
    {
        return $this->resolveLayoutByClassName($classname);
    }

    public function getIdByClassName(string $className): int
    {
        return $this->resolveLayoutByClassName($className)->getId();
    }

    public function getDefaultForResourceType(int $resourceTypeId): ?ResourceLayoutContainer
    {
        return $this->getAll()
            ->where(fn (ResourceLayoutContainer $container) => $container->getResourceType()->getId() === $resourceTypeId)
            ->first(fn (ResourceLayoutContainer $container) => $container->isDefault());
    }

    public function clearCache(): void
    {
        self::$rawLayoutsCache = null;
        self::$layoutsByClassCache = [];
        self::$layoutsClassesByIdCache = null;
        self::$layoutModelsCache = null;
        self::$resourceFieldsCache = null;
    }

    private function getRawLayouts(): array
    {
        if (self::$rawLayoutsCache !== null) {
            return self::$rawLayoutsCache;
        }

        $lock = Cache::lock(Lock::ResourceLayouts->value);

        if (!$lock->block(5)) {
            throw new \RuntimeException('Resource layouts is being locked');
        }

        try {
            $event = new ResourceLayoutsRegistering();
            Event::dispatch($event);

            if (config('cms.discover_layouts', true)) {
                $layoutsPath = (string) config('cms.layouts_path');

                foreach (
                    Collection::make($this->classesDiscoverer->discover($layoutsPath))
                        ->filter(fn (string $className) => is_subclass_of($className, ResourceLayoutInterface::class))
                        ->sort()
                        ->values() as $className
                ) {
                    /** @var ResourceLayoutInterface $layout */
                    $layout = app($className);
                    $event->addLayout($layout);
                }
            }

            $layoutsByClass = [];

            foreach ($event->getLayouts() as $resourceLayout) {
                $layoutsByClass[$resourceLayout::class] = $resourceLayout;
            }

            ksort($layoutsByClass);
            self::$rawLayoutsCache = $layoutsByClass;
        } finally {
            $lock->release();
        }

        return self::$rawLayoutsCache;
    }

    private function resolveLayoutByClassName(string $className): ResourceLayoutContainer
    {
        if (isset(self::$layoutsByClassCache[$className])) {
            return self::$layoutsByClassCache[$className];
        }

        $rawLayouts = $this->getRawLayouts();

        if (!isset($rawLayouts[$className])) {
            throw new \RuntimeException("Resource layout with class '{$className}' not found.");
        }

        $resourceLayout = $rawLayouts[$className];
        $layoutModel = $this->getModelForResourceLayout($resourceLayout);
        $schema = $resourceLayout->getSchema();

        $layoutContainer = new ResourceLayoutContainer(
            id: $layoutModel->getKey(),
            layout: $resourceLayout,
            isDefault: false,
        );

        $this->initFieldModelsCache();

        foreach ($this->normalizeSchema($schema) as $schemaElement) {
            $layoutContainer->addSchemaElement($schemaElement);
        }

        $this->addLayoutToCache($layoutContainer, $layoutModel);

        event(new ResourceLayoutResolved($layoutContainer));

        return $layoutContainer;
    }

    private function getModelForResourceLayout(ResourceLayoutInterface $resourceLayout): ResourceLayout
    {
        $this->initLayoutModelsCache();
        $layoutClass = $resourceLayout::class;

        if (isset(self::$layoutModelsCache[$layoutClass])) {
            return self::$layoutModelsCache[$layoutClass];
        }

        $resourceTypeId = $this->resourceTypeRepository->getIdByClassName($resourceLayout->getResourceType());
        $classModifiedAt = Carbon::createFromTimestamp(getClassFileModifiedAt($resourceLayout::class));

        return ResourceLayout::query()->updateOrCreate([
            'class' => $layoutClass,
        ], [
            'resource_type_id' => $resourceTypeId,
            'class_modified_at' => $classModifiedAt,
        ]);
    }

    private function initLayoutModelsCache(): void
    {
        if (self::$layoutModelsCache === null) {
            $models = ResourceLayout::query()->get();

            self::$layoutModelsCache = $models->keyBy('class')->all();

            self::$layoutsClassesByIdCache = $models
                ->keyBy('id')
                ->map(fn (ResourceLayout $layout) => $layout->class)
                ->toArray();
        }
    }

    private function addLayoutToCache(ResourceLayoutContainer $layoutContainer, ResourceLayout $model): void
    {
        $classname = $layoutContainer->getLayout()::class;

        self::$layoutsByClassCache[$classname] = $layoutContainer;

        self::$layoutModelsCache[$classname] = $model;

        self::$layoutsClassesByIdCache[$layoutContainer->getId()] = $classname;
    }

    private function initFieldModelsCache(): void
    {
        if (self::$resourceFieldsCache === null) {
            self::$resourceFieldsCache = ResourceField::query()
                ->get()
                ->keyBy(fn (ResourceField $resourceField) => $this->getFieldCacheKey($resourceField->key, $resourceField->type))
                ->all();
        }
    }

    private function getModelForField(FieldInterface $field): ResourceField
    {
        $cacheKey = $this->getFieldCacheKey($field->getKey(), $field->getFieldType()->getType());

        if (isset(self::$resourceFieldsCache[$cacheKey])) {
            return self::$resourceFieldsCache[$cacheKey];
        }

        $fieldModel = ResourceField::query()->updateOrCreate([
            'key' => $field->getKey(),
            'type' => $field->getFieldType()->getType(),
        ]);

        if (self::$resourceFieldsCache === null) {
            self::$resourceFieldsCache = [];
        }

        self::$resourceFieldsCache[$cacheKey] = $fieldModel;

        return $fieldModel;
    }

    private function getFieldCacheKey(string $key, string $type): string
    {
        return $key . '::' . $type;
    }

    /**
     * @param array<FormElementInterface> $schema
     */
    private function normalizeSchema(array $schema): Collection
    {
        $rootFields = Collection::make($schema)
            ->filter(fn (FormElementInterface $element) => $element instanceof FieldInterface)
            ->toArray();

        $mainTab = Collection::make($schema)
            ->first(fn (FormElementInterface $element) => $element instanceof Tab);

        if (!$mainTab) {
            $mainTab = Tab::make($this->getMainTabName());
            array_unshift($schema, $mainTab);
        }

        if (!empty($rootFields)) {
            $mainTab->schema(array_merge($mainTab->getSchema(), $rootFields));
        }

        $tabs = Collection::make();

        foreach ($schema as $formElement) {
            if ($formElement instanceof Tab) {
                $formElement->schema(
                    Collection::make($formElement->getSchema())
                        ->map(function (mixed $element) {
                            if (!$element instanceof FieldInterface) {
                                throw new \RuntimeException($element::class . ' must be instance of FieldInterface');
                            }

                            return new FieldContainer(
                                id: $this->getModelForField($element)->getKey(),
                                field: $element,
                            );
                        })
                        ->toArray(),
                );

                $tabs->add($formElement);
            }
        }

        return $tabs;
    }
}
