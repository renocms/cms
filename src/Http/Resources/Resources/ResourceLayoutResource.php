<?php

namespace Reno\Cms\Http\Resources\Resources;

use Illuminate\Http\Request;
use Reno\Cms\FormElements\Tab;
use Illuminate\Support\Collection;
use Reno\Cms\Containers\FieldContainer;
use Illuminate\Http\Resources\Json\JsonResource;
use Reno\Cms\Containers\ResourceLayoutContainer;
use Reno\Cms\Interfaces\Forms\FormElementInterface;
use function PHPSTORM_META\map;

class ResourceLayoutResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ResourceLayoutContainer $layoutContainer */
        $layoutContainer = $this->resource;
        $layout = $layoutContainer->getLayout();

        return [
            'id' => $layoutContainer->getId(),
            'resource_type_id' => $layoutContainer->getResourceType()->getId(),
            'name' => $layout->getName(),
            'children_layouts' => $layoutContainer->getChildrenLayouts()
                ?->map(fn (ResourceLayoutContainer $child) => ResourceLayoutSimpleResource::make($child)->resolve())
                ->toArray(),
            'allow_children' => $layout->allowChildren(),
            'is_catalog' => $layoutContainer->getLayout()->getAttachedEntity() || $layoutContainer->getLayout()->getResourceCatalog(),
            'schema' => $this->buildSchema($layoutContainer),
        ];
    }

    private function buildSchema(ResourceLayoutContainer $layoutContainer): array
    {
        $schema = $layoutContainer->getSchema()->values()
            ->map(fn (FormElementInterface $element, int $index) => $this->elementToArray($element, $index))
            ->toArray();

        $schema[] = $this->makePageSettingsTab();

        return $schema;
    }

    private function makePageSettingsTab(): array
    {
        return [
            'element' => 'tab',
            'name' => __('cms::cms.page_settings_tab'),
            'description' => null,
            'schema' => [
                $this->makeSystemField('resource_layout_id', __('cms::cms.resource_layout'), true),
                $this->makeSystemField('slug', __('cms::cms.slug'), true),
                $this->makeSystemField('status', __('cms::cms.status')),
                $this->makeSystemField('sort_order', __('cms::cms.sort_order')),
                $this->makeSystemField('show_in_menu', __('cms::cms.show_in_menu')),
            ],
        ];
    }

    private function makeSystemField(string $systemKey, string $name, bool $isRequired = false): array
    {
        return [
            'element' => 'field',
            'id' => null,
            'key' => $systemKey,
            'name' => $name,
            'description' => null,
            'type' => 'system',
            'is_required' => $isRequired,
            'sort_order' => 0,
            'js_module' => null,
            'configuration' => [],
            'is_system' => true,
            'system_key' => $systemKey,
        ];
    }

    private function elementToArray(FormElementInterface|FieldContainer $element, int $index): array
    {
        return match ($element::class) {
            Tab::class => $this->tabToArray($element),
            FieldContainer::class => $this->fieldToArray($element, $index),
            default => throw new \RuntimeException('Unsupported element type ' . $element::class),
        };
    }

    private function tabToArray(Tab $tab): array
    {
        return [
            'element' => 'tab',
            'name' => $tab->getName(),
            'description' => $tab->getDescription(),
            'schema' => Collection::make($tab->getSchema())->values()
                ->map(fn (FormElementInterface|FieldContainer $element, int $index) => $this->elementToArray($element, $index)),
        ];
    }

    private function fieldToArray(FieldContainer $fieldContainer, int $index): array
    {
        $result = ResourceLayoutFieldResource::make($fieldContainer)->resolve();
        $result['sort_order'] = $index;
        return $result;
    }
}
