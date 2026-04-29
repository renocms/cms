<?php

namespace Reno\Cms\DTO\Resources;

use Reno\Cms\DTO\Resources\ResourceValueForEdit;
use Reno\Cms\Http\Requests\Resources\ResourceUpdateRequest;
use Reno\Cms\Http\Requests\Resources\BaseResourceUpdateRequest;

class ResourceForEdit
{
    /**
     * @param ResourceValueForEdit[] $values
     */
    public function __construct(
        public readonly ?int $resourceLayoutId,
        public readonly ?string $slug,
        public readonly ?string $status,
        public readonly ?int $sortOrder,
        public readonly ?bool $showInMenu,
        public readonly array $values,
    )
    {
    }

    public static function createFromRequest(BaseResourceUpdateRequest $request): self
    {
        $validated = $request->validated();

        $values = [];
        if ($request instanceof ResourceUpdateRequest && isset($validated['values']) && is_array($validated['values'])) {
            foreach ($validated['values'] as $valueData) {
                $values[] = ResourceValueForEdit::fromArray($valueData);
            }
        }

        return new self(
            resourceLayoutId: $validated['resource_layout_id'] ?? null,
            slug: $validated['slug'] ?? null,
            status: $validated['status'] ?? null,
            sortOrder: $validated['sort_order'] ?? null,
            showInMenu: isset($validated['show_in_menu']) ? (bool) $validated['show_in_menu'] : null,
            values: $values,
        );
    }
}
