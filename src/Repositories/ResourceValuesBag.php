<?php

namespace Reno\Cms\Repositories;

use Reno\Cms\Fields\Media;
use Reno\Cms\Models\Media as MediaModel;
use Illuminate\Support\Arr;
use Reno\Cms\Models\Resource;
use Reno\Cms\Fields\Repeater;
use Illuminate\Support\Collection;
use Reno\Cms\Models\ResourceValue;
use Reno\Cms\Helpers\SchemaHelper;
use Reno\Cms\Containers\FieldContainer;
use Reno\Cms\Fields\CheckboxGroup;
use Reno\Cms\Containers\ResourceValueContainer;
use Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface;

class ResourceValuesBag extends Collection
{
    public function __construct(Resource $resource)
    {
        if (!$resource->relationLoaded('resourceValues')) {
            throw new \RuntimeException('Relation resourceValues should be loaded!');
        }

        /** @var ResourceLayoutRepositoryInterface $resourceLayoutRepository */
        $resourceLayoutRepository = resolve(ResourceLayoutRepositoryInterface::class);
        $layoutContainer = $resourceLayoutRepository->findById($resource->resource_layout_id);

        $values = $resource->resourceValues
            ->keyBy('resourceField.key');

        $items = [];

        /** @var ResourceValue $model */
        /** @var FieldContainer $fieldContainer */
        foreach ($layoutContainer->getFields() as $fieldContainer) {
            $fieldName = $fieldContainer->getField()->getKey();
            $model = $values[$fieldName] ?? null;
            $value = $model?->value ?? null;

            if ($fieldContainer->getField() instanceof Media && $value) {
                if (!$model->relationLoaded('media') || !$model->media) {
                    throw new \RuntimeException("Media should be loaded for media field $model->id");
                }

                $value = $model->media;
            }

            if ($fieldContainer->getField() instanceof CheckboxGroup) {
                $value = json_decode($value, true);
            }

            if ($fieldContainer->getField() instanceof Repeater) {
                $fields = collect($fieldContainer->getField()->getSchema());
                $fieldValues = $value ? json_decode($value, true) : [];

                $mediaFields = [];
                foreach ($fieldValues as $index => $rowValue) {
                    foreach (SchemaHelper::collectMediaFields($fields, $rowValue, "$index.") as $mediaId => $rowKey) {
                        $mediaFields[$mediaId] = $rowKey;
                    }
                }

                if (!empty($mediaFields)) {
                    MediaModel::query()
                        ->whereIn('id', array_keys($mediaFields))
                        ->get()
                        ->each(function (MediaModel $media) use ($mediaFields, &$fieldValues) {
                            Arr::set($fieldValues, $mediaFields[$media->id], $media);
                        });
                }

                $value = $fieldValues;
            }

            $items[$fieldName] = new ResourceValueContainer(
                name: $fieldName,
                value: $value,
                fieldContainer: $fieldContainer,
                model: $model,
            );
        }

        parent::__construct($items);
    }

    /** @return ResourceValueContainer */
    public function get($key, $default = null)
    {
        return parent::get($key, $default);
    }
}
