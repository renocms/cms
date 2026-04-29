<?php

namespace Reno\Cms\DTO\Media;

use Illuminate\Http\UploadedFile;
use Reno\Cms\Http\Requests\Media\MediaStoreRequest;

class MediaForCreate
{
    public function __construct(
        public readonly UploadedFile $file,
        public readonly ?string $name,
        public readonly ?string $altText,
        public readonly ?string $description,
    )
    {
    }

    /**
     * Создать DTO из валидированных данных запроса
     *
     * @param MediaStoreRequest $request
     * @return self
     */
    public static function createFromRequest(MediaStoreRequest $request): self
    {
        $data = $request->validated();

        return new self(
            file: $request->file('file'),
            name: $data['name'] ?? null,
            altText: $data['alt_text'] ?? null,
            description: $data['description'] ?? null,
        );
    }
}
