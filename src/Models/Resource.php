<?php

namespace Reno\Cms\Models;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Reno\Cms\Repositories\ResourceValuesBag;
use Illuminate\Database\Eloquent\Collection;
use Reno\Cms\Containers\ResourceLayoutContainer;
use Reno\Cms\Interfaces\Services\PathCacheInterface;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Reno\Cms\Helpers\TablePrefixHelper;
use Reno\Cms\Interfaces\Resources\ResourceInterface;
use Reno\Cms\Interfaces\Repositories\SettingRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\ResourceRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface;

/**
 * @property int $id
 * @property int $context_id
 * @property int $resource_type_id
 * @property int|null $resource_layout_id
 * @property int|null $parent_id
 * @property bool $is_folder
 * @property bool $show_in_menu
 * @property string $slug
 * @property string $status
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $published_at
 * @property int $author_id
 * @property int|null $editor_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read User $author
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Resource> $children
 * @property-read int|null $children_count
 * @property-read \Reno\Cms\Models\Context $context
 * @property-read User|null $editor
 * @property-read Resource|null $parent
 * @property-read \Reno\Cms\Models\ResourceLayout|null $resourceLayout
 * @property-read \Reno\Cms\Models\ResourceType $resourceType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Reno\Cms\Models\ResourceValue> $resourceValues
 * @property-read int|null $resource_values_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereContextId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereEditorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereIsFolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereShowInMenu($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource wherePublishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereResourceLayoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereResourceTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Resource withoutTrashed()
 * @mixin \Eloquent
 */
class Resource extends Model implements ResourceInterface
{
    use SoftDeletes;

    public static function getTableName(): string
    {
        return TablePrefixHelper::table('resources');
    }

    public function getTable(): string
    {
        return static::getTableName();
    }

    protected $fillable = [
        'context_id',
        'resource_type_id',
        'resource_layout_id',
        'parent_id',
        'slug',
        'status',
        'sort_order',
        'published_at',
        'author_id',
        'editor_id',
        'is_folder',
        'show_in_menu',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_folder' => 'boolean',
        'show_in_menu' => 'boolean',
    ];

    private ?ResourceValuesBag $valuesBag = null;

    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    public function resourceType(): BelongsTo
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Resource::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Resource::class, 'parent_id')->orderBy('sort_order');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'editor_id');
    }

    public function resourceLayout(): BelongsTo
    {
        return $this->belongsTo(ResourceLayout::class);
    }

    public function resourceValues(): HasMany
    {
        return $this->hasMany(ResourceValue::class);
    }

    public function getValuesBag(): ResourceValuesBag
    {
        if ($this->valuesBag === null) {
            $this->valuesBag = new ResourceValuesBag($this);
        }

        return $this->valuesBag;
    }

    public function getAttribute($key)
    {
        if (!$key) {
            return null;
        }

        if ($this->hasAttribute($key)) {
            return $this->getAttributeValue($key);
        }

        if ($this->isRelation($key) || $this->relationLoaded($key)) {
            return $this->getRelationValue($key);
        }

        if ($this->getValuesBag()->has($key)) {
            return $this->getValuesBag()->get($key)->getValue();
        }

        if (method_exists(self::class, $key)) {
            return $this->throwMissingAttributeExceptionIfApplicable($key);
        }

        return $this->throwMissingAttributeExceptionIfApplicable($key);
    }

    public function getId(): int
    {
        return $this->getKey();
    }

    public function getTitle(): ?string
    {
        return $this->getValuesBag()->get('title')->getValue();
    }

    public function hasValue(string $field): bool
    {
        return $this->getValuesBag()->has($field);
    }

    public function getValue(string $field): mixed
    {
        return $this->getValuesBag()->get($field)?->getValue();
    }

    public function getAvailableValues(string $field): array
    {
        $configuration = $this->getValuesBag()->get($field)?->getFieldContainer()->getField()->getConfiguration() ?? [];
        return $configuration['options'] ?? [];
    }

    public function getUrl(): ?string
    {
        /** @var PathCacheInterface $pathCacheService */
        $pathCacheService = resolve(PathCacheInterface::class);

        if ($this->is_folder) {
            return $pathCacheService->getPathByResourceId($this->context_id, $this->id);
        } else if ($this->parent_id) {
            return $pathCacheService->getPathByResourceId($this->context_id, $this->parent_id) . '/' . $this->slug;
        }

        /** @var SettingRepositoryInterface $settingsRepository */
        $settingsRepository = resolve(SettingRepositoryInterface::class);
        if ($this->id == $settingsRepository->getHomeResourceId($this->context_id)) {
            return '/';
        }

        return $this->slug;
    }

    public function getResourceLayout(): ResourceLayoutContainer
    {
        /** @var ResourceLayoutRepositoryInterface $repository */
        $repository = resolve(ResourceLayoutRepositoryInterface::class);
        return $repository->findById($this->resource_layout_id);
    }

    public function getUltimateParentId(int $level): ?int
    {
        $parentIds = $this->getParentIds();
        array_unshift($parentIds, $this->id);
        $ids = array_slice($parentIds, 0, $level + 1);
        return Arr::last($ids);
    }

    public function getParents(): Collection
    {
        $cacheKey = 'parents:' . $this->id;

        if (Cache::store('cms')->has($cacheKey)) {
            return Cache::store('cms')->get($cacheKey);
        }

        /** @var ResourceRepositoryInterface $resourceRepository */
        $resourceRepository = resolve(ResourceRepositoryInterface::class);
        $resourceIds = $this->getParentIds();
        $resources = $resourceRepository->get($resourceIds)
            ->sort(fn (Resource $res) => array_search($res->id, $resourceIds));

        Cache::store('cms')->put($cacheKey, $resources, config('cms.store.ttl'));
        return $resources;
    }

    public function getParentIds(): array
    {
        $cacheKey = 'parentIds:' . $this->id;

        if (Cache::store('cms')->has($cacheKey)) {
            return Cache::store('cms')->get($cacheKey);
        }

        $ids = [];

        $resource = $this;
        while ($resource->parent_id) {
            $ids[] = $resource->parent_id;
            if (!$resource->relationLoaded('parent')) {
                $resource->load('parent');
            }
            $resource = $resource->parent;
        }

        Cache::store('cms')->put($cacheKey, $ids, config('cms.store.ttl'));
        return $ids;
    }

    public function hasChildren(): bool
    {
        return $this->relationLoaded('children') && $this->children->isNotEmpty();
    }


    /**
     * Вычислить полный путь ресурса
     *
     * @return string|null
     */
    public function calculatePath(): ?string
    {
        if (!$this->slug) {
            return null;
        }

        if ($this->parent_id === null) {
            return '/' . $this->slug;
        }

        // Загружаем родителя, если он не загружен
        if (!$this->relationLoaded('parent')) {
            $this->load('parent');
        }

        $parent = $this->parent;
        if (!$parent) {
            return '/' . $this->slug;
        }

        $parentPath = $parent->calculatePath();
        if (!$parentPath) {
            return '/' . $this->slug;
        }

        return rtrim($parentPath, '/') . '/' . $this->slug;
    }

    /**
     * Пересохранить путь ресурса в кэш
     *
     * @return void
     */
    public function refreshPathCache(): void
    {
        /** @var \Reno\Cms\Interfaces\Services\PathCacheInterface $pathCache */
        $pathCache = app(\Reno\Cms\Interfaces\Services\PathCacheInterface::class);

        // Находим старый путь в кэше
        $oldPath = $pathCache->getPathByResourceId($this->context_id, $this->id);
        
        // Вычисляем новый путь
        $newPath = $this->calculatePath();

        // Удаляем старый путь, если он существует и отличается от нового
        if ($oldPath && $oldPath !== $newPath) {
            $pathCache->forget($this->context_id, $oldPath);
        }

        // Если ресурс опубликован, является папкой и есть путь, сохраняем его в кэш
        if ($newPath && $this->status === 'published' && ($this->is_folder || !$this->parent_id)) {
            $pathCache->put($this->context_id, $newPath, $this->id);
        } elseif ($oldPath) {
            // Если ресурс не опубликован или не папка, удаляем путь из кэша
            $pathCache->forget($this->context_id, $oldPath);
        }
    }
}

