<?php

namespace Reno\Cms\DTO\Media;

use Reno\Cms\Http\Requests\Media\MediaUpdateRequest;

class MediaForUpdate
{
    public function __construct(
        public readonly ?string $altText,
        public readonly ?string $description,
    )
    {
    }

    /**
     * Создать DTO из валидированных данных запроса
     *
     * @param MediaUpdateRequest $request
     * @return self
     */
    public static function createFromRequest(MediaUpdateRequest $request): self
    {
        $data = $request->validated();

        return new self(
            altText: $data['alt_text'] ?? null,
            description: $data['description'] ?? null,
        );
    }
}
