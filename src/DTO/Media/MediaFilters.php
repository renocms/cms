<?php

namespace Reno\Cms\DTO\Media;

use Reno\Cms\Http\Requests\Media\MediaIndexRequest;

class MediaFilters
{
    public function __construct(
        public readonly ?string $search,
        public readonly ?string $mimeType,
    )
    {
    }

    /**
     * Создать DTO из валидированных данных запроса
     *
     * @param MediaIndexRequest $request
     * @return self
     */
    public static function createFromRequest(MediaIndexRequest $request): self
    {
        $data = $request->validated();

        return new self(
            search: $data['search'] ?? null,
            mimeType: $data['mime_type'] ?? null,
        );
    }
}
