<?php

namespace Reno\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Reno\Cms\Helpers\TablePrefixHelper;
use Reno\Cms\Interfaces\Repositories\FieldTypeRepositoryInterface;

/**
 * @property int $id
 * @property string $key
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Reno\Cms\Models\ResourceValue> $resourceValues
 * @property-read int|null $resource_values_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceField query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceField whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceField whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ResourceField extends Model
{
    public static function getTableName(): string
    {
        return TablePrefixHelper::table('resource_fields');
    }

    public function getTable(): string
    {
        return static::getTableName();
    }

    protected $fillable = [
        'key',
        'type',
    ];

    public function resourceValues(): HasMany
    {
        return $this->hasMany(ResourceValue::class);
    }

    public function getJsModule(): ?string
    {
        if (!$this->type) {
            return null;
        }

        /** @var FieldTypeRepositoryInterface $fieldTypeRepository */
        $fieldTypeRepository = app(FieldTypeRepositoryInterface::class);
        $fieldType = $fieldTypeRepository->findByType($this->type);

        return $fieldType?->getJsModule();
    }
}

