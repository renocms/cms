<?php

namespace Reno\Cms\Containers;

use Illuminate\Support\Collection;
use Reno\Cms\Helpers\SchemaHelper;
use Reno\Cms\Interfaces\Forms\FormElementInterface;
use Reno\Cms\Interfaces\Layouts\ResourceLayoutInterface;
use Reno\Cms\Interfaces\Resources\ResourcesCatalogInterface;
use Reno\Cms\Interfaces\Layouts\LayoutViewComposerInterface;
use Reno\Cms\Interfaces\Repositories\EntitiesRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\ResourceTypeRepositoryInterface;
use Reno\Cms\Interfaces\Repositories\ResourceLayoutRepositoryInterface;

class ResourceLayoutContainer
{
    /** @var Collection<FormElementInterface>|null $schema */
    private ?Collection $schema = null;

    /** @var Collection<FieldContainer>|null $schema */
    private ?Collection $fieldsList = null;

    public function __construct(
        private readonly int $id,
        private readonly ResourceLayoutInterface $layout,
        private readonly bool $isDefault,
    )
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getLayout(): ResourceLayoutInterface
    {
        return $this->layout;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function getResourceType(): ResourceTypeContainer
    {
        /** @var ResourceTypeRepositoryInterface $resourceTypeRepository */
        $resourceTypeRepository = app(ResourceTypeRepositoryInterface::class);
        return $resourceTypeRepository->findByClassname($this->getLayout()->getResourceType());
    }

    public function addSchemaElement(FormElementInterface $element): void
    {
        if (!$this->schema) {
            $this->schema = Collection::make();
        }

        $this->schema->add($element);
    }

    public function getSchema(): Collection
    {
        return $this->schema ?? Collection::make();
    }

    public function getAttachedEntity(): ?ResourcesCatalogContainer
    {
        $entityClass = $this->getLayout()->getAttachedEntity();

        if (!$entityClass) {
            return null;
        }

        /** @var EntitiesRepositoryInterface $entitiesRepository */
        $entitiesRepository = resolve(EntitiesRepositoryInterface::class);
        return $entitiesRepository->findByClassname($entityClass);
    }

    public function isCatalog(): bool
    {
        return $this->getLayout()->getAttachedEntity() || $this->getLayout()->getResourceCatalog();
    }

    public function getResourceCatalog(): ?ResourcesCatalogInterface
    {
        if ($catalogClass = $this->getLayout()->getResourceCatalog()) {
            return resolve($catalogClass);
        }

        return null;
    }

    public function getChildrenLayouts(): ?Collection
    {
        $layoutClasses = $this->getLayout()->getChildrenLayouts();

        if (!$layoutClasses) {
            return null;
        }

        /** @var ResourceLayoutRepositoryInterface $resourceLayoutRepository */
        $resourceLayoutRepository = app(ResourceLayoutRepositoryInterface::class);
        $result = Collection::make();

        foreach ($layoutClasses as $layoutClass) {
            $result->add($resourceLayoutRepository->findByClassname($layoutClass));
        }

        return $result;
    }

    public function getViewComposer(): ?LayoutViewComposerInterface
    {
        $composer = $this->layout->getViewComposer();
        return $composer ? resolve($composer) : null;
    }

    public function getField(string $name): FieldContainer
    {
        $fieldsList = $this->getFields();

        if (!$fieldsList->has($name)) {
            throw new \RuntimeException('Field "' . $name . '" does not exist.');
        }

        return $fieldsList->get($name);
    }

    public function getFields(): Collection
    {
        if ($this->fieldsList === null) {
            $this->fieldsList = SchemaHelper::getFields($this->getSchema()->toArray())
                ->keyBy(fn (FieldContainer $fieldContainer) => $fieldContainer->getField()->getKey());
        }

        return $this->fieldsList;
    }
}
