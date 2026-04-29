<?php

namespace Reno\Cms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Reno\Cms\Helpers\TablePrefixHelper;

/**
 * @property int $id
 * @property int $resource_layout_id
 * @property int $resource_field_id
 * @property bool $is_required
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Reno\Cms\Models\ResourceField $resourceField
 * @property-read \Reno\Cms\Models\ResourceLayout $resourceLayout
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayoutField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayoutField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayoutField query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayoutField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayoutField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayoutField whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayoutField whereResourceFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayoutField whereResourceLayoutId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayoutField whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResourceLayoutField whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ResourceLayoutField extends Model
{
    public static function getTableName(): string
    {
        return TablePrefixHelper::table('resource_layout_fields');
    }

    public function getTable(): string
    {
        return static::getTableName();
    }

    protected $fillable = [
        'resource_layout_id',
        'resource_field_id',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function resourceLayout(): BelongsTo
    {
        return $this->belongsTo(ResourceLayout::class);
    }

    public function resourceField(): BelongsTo
    {
        return $this->belongsTo(ResourceField::class, 'resource_field_id');
    }
}

