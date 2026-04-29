<?php

namespace Reno\Cms\Services\Resources;

use Reno\Cms\Models\Resource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Reno\Cms\Interfaces\Repositories\ResourceTypeRepositoryInterface;

class ResourcesHydrator
{
    public function __construct(
        protected ResourceTypeRepositoryInterface $resourceTypeRepository,
    )
    {
    }

    public function hydrateResources(Collection $resources, ?array $onlyFields = null): void
    {
        $resourceTypeIds = $resources->pluck('resource_type_id')->unique()->toArray();

        foreach ($resourceTypeIds as $resourceTypeId) {
            $resourceTypeContainer = $this->resourceTypeRepository->findById($resourceTypeId);
            $resourceClass = $resourceTypeContainer->getResourceType()->getResourceClass();
            $newResources = $resources->where('resource_type_id', $resourceTypeId);

            if ($resourceClass != Resource::class) {
                /** @var Resource $oldResource */
                foreach ($newResources as $i => $oldResource) {
                    $newResource = new $resourceClass;
                    $newResource->setRawAttributes($oldResource->getAttributes());
                    $newResources[$i] = $newResource;
                }
            }

            $relations = array_merge(
                $this->getBaseRelations(),
                $resourceTypeContainer->getResourceType()->getResourceRelations(),
            );

            if ($resourceTypeContainer->getResourceType()->supportsResourceFields() && $onlyFields !== null) {
                $relations = array_combine($relations, $relations);

                if (isset($relations['resourceValues'])) {
                    unset($relations['resourceValues']);
                    $relations = array_values($relations);

                    $newResources->load(['resourceValues' => function (HasMany $query) use ($onlyFields) {
                        $query->whereHas('resourceField', function (Builder $query) use ($onlyFields) {
                            $query->whereIn('key', $onlyFields);
                        });
                    }]);
                }
            }

            $newResources->load($relations);

            foreach ($newResources as $i => $newResource) {
                $resources[$i] = $newResource;
            }
        }
    }

    private function getBaseRelations(): array
    {
        return [];
    }
}
