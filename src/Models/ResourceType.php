<?php

namespace Reno\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Reno\Cms\Helpers\TablePrefixHelper;
use Reno\Cms\Interfaces\Repositories\ResourceTypeRepositoryInterface;

/**
 * @property int $id
 * @property string|null $class
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Reno\Cms\Models\Resource> $resources
 * @property-read int|null $resources_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceType whereClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceType whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ResourceType extends Model
{
    public static function getTableName(): string
    {
        return TablePrefixHelper::table('resource_types');
    }

    public function getTable(): string
    {
        return static::getTableName();
    }

    protected $fillable = [
        'class',
    ];

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
    }

    public function getJsModule(): ?string
    {
        try {
            /** @var ResourceTypeRepositoryInterface $resourceTypeRepository */
            $resourceTypeRepository = app(ResourceTypeRepositoryInterface::class);
            $resourceTypeContainer = $resourceTypeRepository->findById($this->getKey());

            return $resourceTypeContainer->getResourceType()->getJsModule();
        } catch (\RuntimeException $e) {
            return null;
        }
    }
}

