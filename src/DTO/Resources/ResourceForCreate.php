<?php

namespace Reno\Cms\DTO\Resources;

use Reno\Cms\DTO\Resources\ResourceValueForEdit;
use Reno\Cms\Http\Requests\Resources\ResourceStoreRequest;
use Reno\Cms\Http\Requests\Resources\BaseResourceStoreRequest;

class ResourceForCreate
{
    /**
     * @param ResourceValueForEdit[] $values
     */
    public function __construct(
        public readonly int $contextId,
        public readonly int $resourceTypeId,
        public readonly ?int $parentId,
        public readonly ?int $resourceLayoutId,
        public readonly string $slug,
        public readonly string $status,
        public readonly int $sortOrder,
        public readonly bool $showInMenu,
        public readonly ?array $values = [],
    )
    {
    }

    public static function createFromRequest(BaseResourceStoreRequest $request): self
    {
        $validated = $request->validated();

        $values = [];
        if ($request instanceof ResourceStoreRequest && isset($validated['values']) && is_array($validated['values'])) {
            foreach ($validated['values'] as $valueData) {
                $values[] = ResourceValueForEdit::fromArray($valueData);
            }
        }

        return new self(
            contextId: (int) $validated['context_id'],
            resourceTypeId: (int) $validated['resource_type_id'],
            parentId: isset($validated['parent_id']) ? (int) $validated['parent_id'] : null,
            resourceLayoutId: isset($validated['resource_layout_id']) ? (int) $validated['resource_layout_id'] : null,
            slug: $validated['slug'],
            status: $validated['status'] ?? 'draft',
            sortOrder: isset($validated['sort_order']) ? (int) $validated['sort_order'] : 0,
            showInMenu: isset($validated['show_in_menu']) ? (bool) $validated['show_in_menu'] : true,
            values: $values,
        );
    }
}
