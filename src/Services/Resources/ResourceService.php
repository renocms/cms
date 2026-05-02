<?php

namespace Reno\Cms\Services\Resources;

use Illuminate\Support\Facades\DB;
use Reno\Cms\Helpers\SchemaHelper;
use Illuminate\Support\Facades\Event;
use Reno\Cms\Helpers\ValidatorHelper;
use Illuminate\Support\Facades\Validator;
use Reno\Cms\DTO\Resources\ResourceForCreate;
use Reno\Cms\DTO\Resources\ResourceForEdit;
use Reno\Cms\DTO\Resources\ResourceValueForEdit;
use Reno\Cms\Events\Resources\ResourceCreated;
use Reno\Cms\Events\Resources\ResourceCreating;
use Reno\Cms\Events\Resources\ResourceDeleted;
use Reno\Cms\Events\Resources\ResourceDeleting;
use Reno\Cms\Events\Resources\ResourceInitializing;
use Reno\Cms\Events\Resources\ResourceMoved;
use Reno\Cms\Events\Resources\ResourceMoving;
use Reno\Cms\Events\Resources\ResourcePublishingStateChanged;
use Reno\Cms\Events\Resources\ResourceUpdated;
use Reno\Cms\Events\Resources\ResourceUpdating;
use Reno\Cms\Interfaces\FieldTypes\FieldTypeInterface;
use Reno\Cms\Interfaces\FieldTypes\SyncsResourceValueInterface;
use Reno\Cms\Interfaces\Repositories\FieldTypeRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\ResourceRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\SettingRepositoryInterface;
use Reno\Cms\Exceptions\HomeResourceCannotBeDeletedException;
use Reno\Cms\Interfaces\Services\PathCacheInterface;
use Reno\Cms\Interfaces\Services\ResourceServiceInterface;
use Reno\Cms\Interfaces\Services\ResourceVersionServiceInterface;
use Reno\Cms\Models\Context;
use Reno\Cms\Models\Resource;
use Reno\Cms\Models\ResourceField;
use Reno\Cms\Models\ResourceLayout;
use Reno\Cms\Models\ResourceValue;
use Reno\Cms\Interfaces\Repositories\ResourceTypeRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface;
use Reno\Cms\Containers\ResourceLayoutContainer;

class ResourceService implements ResourceServiceInterface
{
    public function __construct(
        protected ResourceVersionServiceInterface $resourceVersionService,
        protected PathCacheInterface $pathCache,
        protected ResourceRepositoryInterface $resourceRepository,
        protected ResourceTypeRepositoryInterface $resourceTypeRepository,
        protected ResourceLayoutRepositoryInterface $resourceLayoutRepository,
        protected FieldTypeRepositoryInterface $fieldTypeRepository,
        protected SettingRepositoryInterface $settingRepository,
    )
    {
    }
    
    public function create(ResourceForCreate $dto): Resource
    {
        $this->validateValues($dto->resourceLayoutId, $dto->values);

        return DB::transaction(function () use ($dto) {
            Event::dispatch(new ResourceCreating($dto));

            // Создаем ресурс
            $resource = Resource::create([
                'context_id' => $dto->contextId,
                'resource_type_id' => $dto->resourceTypeId,
                'parent_id' => $dto->parentId,
                'resource_layout_id' => $dto->resourceLayoutId,
                'slug' => $dto->slug,
                'status' => $dto->status,
                'sort_order' => $dto->sortOrder,
                'show_in_menu' => $dto->showInMenu,
                'author_id' => auth()->id(),
            ]);

            // Обновляем значения
            $this->syncResourceValues($resource->id, $dto->values);

            $relations = $this->resourceTypeRepository->findById($dto->resourceTypeId)
                ->getResourceType()
                ->getResourceRelations();

            $resource = $resource->fresh()->load($relations);

            // Добавляем путь в кэш только для опубликованных папок
            if ($resource->status === 'published' && $resource->is_folder) {
                $path = $resource->calculatePath();
                if ($path) {
                    $this->pathCache->put($resource->context_id, $path, $resource->id);
                }
            }

            // Обновляем is_folder у родителя
            if ($dto->parentId !== null) {
                $this->updateParentIsFolder($dto->parentId);
            }

            Event::dispatch(new ResourceCreated($resource));

            return $resource;
        });
    }

    public function delete(int $id): bool
    {
        $resource = $this->findById($id);
        if (!$resource) {
            return false;
        }

        $homeResourceId = $this->settingRepository->getHomeResourceId((int) $resource->context_id);
        if ($homeResourceId !== null && $homeResourceId === (int) $resource->id) {
            throw new HomeResourceCannotBeDeletedException();
        }

        $path = $resource->calculatePath();
        $contextId = $resource->context_id;
        $parentId = $resource->parent_id;

        Event::dispatch(new ResourceDeleting($resource));

        $result = $resource->delete();

        if ($result && $path) {
            $this->pathCache->forget($contextId, $path);
            $this->updateChildrenPaths($resource);
        }

        // Обновляем is_folder у родителя после удаления
        if ($result && $parentId !== null) {
            $this->updateParentIsFolder($parentId);
        }

        if ($result) {
            Event::dispatch(new ResourceDeleted($resource));
        }

        return $result;
    }

    public function findById(int $id): ?Resource
    {
        return Resource::find($id);
    }

    public function update(int $id, ResourceForEdit $dto): Resource
    {
        $this->validateValues($dto->resourceLayoutId, $dto->values);

        return DB::transaction(function () use ($id, $dto) {
            $resource = $this->findById($id);
            if (!$resource) {
                throw new \RuntimeException('Resource not found');
            }

            Event::dispatch(new ResourceUpdating($resource, $dto));

            // Создаем версию
            $this->resourceVersionService->create($resource->id);

            $updateData = [];
            if ($dto->resourceLayoutId !== null) {
                $updateData['resource_layout_id'] = $dto->resourceLayoutId;
            }
            if ($dto->slug !== null) {
                $updateData['slug'] = $dto->slug;
            }
            if ($dto->status !== null) {
                $updateData['status'] = $dto->status;
            }
            if ($dto->sortOrder !== null) {
                $updateData['sort_order'] = $dto->sortOrder;
            }
            if ($dto->showInMenu !== null) {
                $updateData['show_in_menu'] = $dto->showInMenu;
            }
            $updateData['editor_id'] = auth()->id();

            $oldPath = $resource->calculatePath();
            $oldParentId = $resource->parent_id;
            $oldSlug = $resource->slug;
            $oldStatus = $resource->status;

            if (!empty($updateData)) {
                $resource->update($updateData);
            }

            // Обновляем значения
            $this->syncResourceValues($resource->id, $dto->values);

            $relations = $this->resourceTypeRepository->findById($resource->resource_type_id)
                ->getResourceType()
                ->getResourceRelations();

            $resource = $resource->fresh()->load($relations);

            // Обновляем кэш путей, если изменился slug, parent_id или status
            if ($dto->slug !== null || $dto->status !== null || $oldParentId !== $resource->parent_id) {
                $this->updatePathCache($resource, $oldPath, $oldParentId, $oldSlug);
            }

            if ($oldStatus !== $resource->status) {
                Event::dispatch(new ResourcePublishingStateChanged($resource, $oldStatus, $resource->status));
            }

            Event::dispatch(new ResourceUpdated($resource));

            return $resource;
        });
    }

    private function syncResourceValues(int $resourceId, array $values): void
    {
        /** @var ResourceValueForEdit $valueDTO */
        foreach ($values as $valueDTO) {
            $resourceValue = ResourceValue::where('resource_id', $resourceId)
                ->where('resource_field_id', $valueDTO->resourceFieldId)
                ->first();

            $fieldType = $this->fieldTypeRepository->findByFieldId($valueDTO->resourceFieldId);

            if ($this->shouldDeleteValue($valueDTO->value)) {
                if ($resourceValue) {
                    if ($fieldType instanceof SyncsResourceValueInterface) {
                        $fieldType->deleteResourceValue($resourceValue);
                    }

                    $resourceValue->delete();
                }

                continue;
            }

            if (!$resourceValue) {
                $resourceValue = ResourceValue::create([
                    'resource_id' => $resourceId,
                    'resource_field_id' => $valueDTO->resourceFieldId,
                    'value' => '',
                ]);
            }

            if ($fieldType instanceof SyncsResourceValueInterface) {
                $fieldType->syncResourceValue($resourceValue, $valueDTO->value);
                continue;
            }

            $preparedValue = $fieldType?->dehydrate($valueDTO->value) ?? $valueDTO->value;
            $serializedValue = $this->serializeResourceValue($preparedValue);

            $resourceValue->update([
                'value' => $serializedValue,
            ]);
        }
    }

    private function shouldDeleteValue(mixed $value): bool
    {
        return $value === null || $value === '';
    }

    private function serializeResourceValue(mixed $value): string
    {
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return (string) $value;
    }

    private function updatePathCache(Resource $resource, ?string $oldPath, ?int $oldParentId, ?string $oldSlug): void
    {
        $contextId = $resource->context_id;
        
        // Находим старый путь в кэше (если он там есть)
        $cachedOldPath = $this->pathCache->getPathByResourceId($contextId, $resource->id);
        
        // Используем старый путь из кэша, если он есть, иначе используем вычисленный
        $pathToRemove = $cachedOldPath ?? $oldPath;
        
        // Вычисляем новый путь
        $newPath = $resource->calculatePath();

        // Удаляем старый путь из кэша, если он существует и отличается от нового
        if ($pathToRemove && $pathToRemove !== $newPath) {
            $this->pathCache->forget($contextId, $pathToRemove);
        }

        // Добавляем новый путь в кэш только для опубликованных папок
        if ($newPath && $resource->status === 'published' && ($resource->is_folder || !$resource->parent_id)) {
            $this->pathCache->put($contextId, $newPath, $resource->id);
        }

        // Если изменился parent_id или slug, обновляем пути дочерних ресурсов
        if ($oldParentId !== $resource->parent_id || $oldSlug !== $resource->slug) {
            $this->updateChildrenPaths($resource);
        }
    }

    private function updateChildrenPaths(Resource $resource): void
    {
        $children = Resource::where('parent_id', $resource->id)->get();
        
        foreach ($children as $child) {
            // Загружаем родителя для корректного вычисления пути
            if (!$child->relationLoaded('parent')) {
                $child->load('parent');
            }

            // Находим старый путь в кэше
            $oldPath = $this->pathCache->getPathByResourceId($child->context_id, $child->id);
            
            // Пересчитываем новый путь для дочернего ресурса
            $newPath = $child->calculatePath();

            // Удаляем старый путь, если он изменился
            if ($oldPath && $oldPath !== $newPath) {
                $this->pathCache->forget($child->context_id, $oldPath);
            }

            // Добавляем новый путь в кэш только для опубликованных папок
            if ($newPath && $child->status === 'published' && $child->is_folder) {
                $this->pathCache->put($child->context_id, $newPath, $child->id);
            }

            // Рекурсивно обновляем пути потомков
            $this->updateChildrenPaths($child);
        }
    }

    public function move(int $id, ?int $parentId, int $sortOrder): Resource
    {
        $resource = $this->findById($id);
        if (!$resource) {
            throw new \RuntimeException('Resource not found');
        }

        Event::dispatch(new ResourceMoving($resource, $parentId, $sortOrder));

        // Проверяем, что ресурс не перемещается в самого себя или в своего потомка
        if ($parentId !== null) {
            $parent = $this->findById($parentId);
            if (!$parent) {
                throw new \RuntimeException('Parent resource not found');
            }
            
            // Проверяем, что родитель не является потомком перемещаемого ресурса
            if ($this->isDescendant($parentId, $id)) {
                throw new \RuntimeException('Cannot move resource into its own descendant');
            }
        }

        $oldPath = $resource->calculatePath();
        $oldParentId = $resource->parent_id;

        // Обновляем parent_id и sort_order
        $resource->update([
            'parent_id' => $parentId,
            'sort_order' => $sortOrder,
            'editor_id' => auth()->id(),
        ]);

        $resource = $resource->fresh();

        // Пересчитываем sort_order для всех siblings на целевом уровне
        $this->reorderSiblings($parentId, $id, $sortOrder);

        // Обновляем кэш путей
        $this->updatePathCache($resource, $oldPath, $oldParentId, $resource->slug);

        // Обновляем is_folder у старого и нового родителя
        if ($oldParentId !== $parentId) {
            if ($oldParentId !== null) {
                $this->updateParentIsFolder($oldParentId);
            }
            if ($parentId !== null) {
                $this->updateParentIsFolder($parentId);
            }
        }

        Event::dispatch(new ResourceMoved($resource, $oldParentId, $parentId));

        return $resource;
    }

    private function isDescendant(int $potentialDescendantId, int $ancestorId): bool
    {
        $current = $this->findById($potentialDescendantId);
        if (!$current) {
            return false;
        }

        while ($current->parent_id !== null) {
            if ($current->parent_id === $ancestorId) {
                return true;
            }
            $current = $this->findById($current->parent_id);
            if (!$current) {
                break;
            }
        }

        return false;
    }

    private function reorderSiblings(?int $parentId, int $movedResourceId, int $targetSortOrder): void
    {
        // Получаем все ресурсы на том же уровне (с тем же parent_id), включая перемещенный
        $allSiblings = Resource::where('parent_id', $parentId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
        
        // Исключаем перемещенный ресурс из списка для пересчета
        $siblings = $allSiblings->filter(function ($sibling) use ($movedResourceId) {
            return $sibling->id !== $movedResourceId;
        });
        
        // Пересчитываем sort_order для всех siblings
        $currentOrder = 0;
        foreach ($siblings as $sibling) {
            // Если достигли целевой позиции, пропускаем её (она займет movedResourceId)
            if ($currentOrder === $targetSortOrder) {
                $currentOrder += 2;
            }
            
            // Обновляем sort_order только если он изменился
            if ($sibling->sort_order !== $currentOrder) {
                $sibling->update(['sort_order' => $currentOrder]);
            }
            
            $currentOrder += 2;
        }
    }

    public function makeDraft(?int $parentId, ?int $contextId = null): Resource
    {
        $parent = null;

        if ($parentId) {
            $parent = $this->resourceRepository->findById($parentId);
        }

        $resource = new Resource();
        $resource->parent_id = $parentId;
        $resource->context_id = $parent?->context_id;
        $resource->resource_type_id = $parent?->resource_type_id;
        $resource->status = 'draft';
        $resource->slug = '';
        $resource->show_in_menu = true;

        if ($parentId === null && $contextId !== null && $contextId > 0) {
            $this->fillRootDraftFromContext($resource, $contextId);
        }

        $siblingsQuery = Resource::query()->where('parent_id', $parentId);
        if ($resource->context_id !== null) {
            $siblingsQuery->where('context_id', $resource->context_id);
        }
        $maxSortOrder = $siblingsQuery->max('sort_order') ?? -1;
        $resource->sort_order = $maxSortOrder + 1;

        if ($resource->resource_type_id) {
            if ($parent !== null) {
                $defaultLayout = $this->resourceLayoutRepository->getDefaultForResourceType($resource->resource_type_id);

                if ($defaultLayout) {
                    $resource->resource_layout_id = $defaultLayout->getId();
                } elseif ($parent->resource_layout_id) {
                    $resource->resource_layout_id = $parent->resource_layout_id;
                }
            } elseif ($resource->resource_layout_id === null) {
                $defaultLayout = $this->resourceLayoutRepository->getDefaultForResourceType($resource->resource_type_id);

                if ($defaultLayout) {
                    $resource->resource_layout_id = $defaultLayout->getId();
                }
            }
        }
        
        $resource->setRelation('resourceValues', collect());
        
        // Создаем событие и диспатчим его
        $event = new ResourceInitializing($resource);
        Event::dispatch($event);
        
        // Загружаем необходимые связи для ResourceEditResource
        if ($resource->context_id) {
            $context = Context::find($resource->context_id);
            if ($context) {
                $resource->setRelation('context', $context);
            }
        }
        
        if ($resource->resource_type_id) {
            $resourceType = \Reno\Cms\Models\ResourceType::find($resource->resource_type_id);
            if ($resourceType) {
                $resource->setRelation('resourceType', $resourceType);
            }
        }
        
        if ($resource->resource_layout_id) {
            $resourceLayout = ResourceLayout::with(['layoutFields.resourceField'])->find($resource->resource_layout_id);
            if ($resourceLayout) {
                $resource->setRelation('resourceLayout', $resourceLayout);
            }
        }
        
        return $resource;
    }

    private function fillRootDraftFromContext(Resource $resource, int $contextId): void
    {
        $existingRoot = Resource::query()
            ->where('context_id', $contextId)
            ->whereNull('parent_id')
            ->orderByDesc('sort_order')
            ->first();

        if ($existingRoot !== null) {
            $resource->context_id = $contextId;
            $resource->resource_type_id = $existingRoot->resource_type_id;
            $resource->resource_layout_id = $existingRoot->resource_layout_id;

            return;
        }

        $rootLayout = $this->firstRootLayoutContainer();
        if ($rootLayout === null) {
            return;
        }

        $resource->context_id = $contextId;
        $resource->resource_type_id = $rootLayout->getResourceType()->getId();
        $resource->resource_layout_id = $rootLayout->getId();
    }

    /**
     * Макеты верхнего уровня (не входят в children_layouts других макетов).
     *
     * @return ResourceLayoutContainer|null
     */
    private function firstRootLayoutContainer(): ?ResourceLayoutContainer
    {
        $layouts = $this->resourceLayoutRepository->getAll();
        $allChildLayoutIds = collect();

        foreach ($layouts as $layout) {
            $children = $layout->getChildrenLayouts();
            if ($children === null) {
                continue;
            }

            foreach ($children as $child) {
                $allChildLayoutIds->add($child->getId());
            }
        }

        $allChildLayoutIds = $allChildLayoutIds->unique()->values();

        return $layouts
            ->reject(fn (ResourceLayoutContainer $layout) => $allChildLayoutIds->contains($layout->getId()))
            ->first();
    }

    /**
     * Обновить флаг is_folder у родителя ресурса
     */
    private function updateParentIsFolder(?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        $parent = $this->findById($parentId);
        if (!$parent) {
            return;
        }

        // Проверяем, есть ли у родителя дочерние ресурсы
        $hasChildren = Resource::where('parent_id', $parentId)->exists();

        // Обновляем флаг is_folder
        if ($parent->is_folder !== $hasChildren) {
            $parent->update(['is_folder' => $hasChildren]);
        }
    }

    /**
     * @throws \Illuminate\Validation\ValidationException
     */
    private function validateValues(int $resourceLayoutId, array $values): void
    {
        $resourceLayout = $this->resourceLayoutRepository->findById($resourceLayoutId);
        $fields = $rules = $data = [];

        foreach (SchemaHelper::getFields($resourceLayout->getSchema()->toArray()) as $fieldContainer) {
            $fieldRules = ValidatorHelper::normalizeRulesArray(
                $fieldContainer->getField()->getKey(),
                $fieldContainer->getField()->getValidationRules(),
            );

            foreach ($fieldRules as $key => $rule) {
                $rules[$key] = $rule;
            }

            $fields[$fieldContainer->getId()] = $fieldContainer->getField()->getKey();
        }

        /** @var ResourceValueForEdit $valueDTO */
        foreach ($values as $valueDTO) {
            $data[ $fields[$valueDTO->resourceFieldId] ] = $valueDTO->value;
        }

        Validator::make($data, $rules)->validate();
    }

}
