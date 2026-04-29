<?php

namespace Reno\Cms\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Reno\Cms\DTO\Media\MediaFilters;
use Reno\Cms\DTO\Media\MediaForCreate;
use Reno\Cms\DTO\Media\MediaForUpdate;
use Reno\Cms\Interfaces\Services\MediaServiceInterface;
use Reno\Cms\Models\Media;

class MediaService implements MediaServiceInterface
{
    public function getAll(MediaFilters $dto): LengthAwarePaginator
    {
        $query = Media::query();

        // Поиск по имени
        if ($dto->search !== null && !empty($dto->search)) {
            $search = $dto->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%");
            });
        }

        // Фильтр по типу MIME
        if ($dto->mimeType !== null && !empty($dto->mimeType)) {
            $mimeType = $dto->mimeType;
            if (str_contains($mimeType, '/')) {
                $query->where('mime_type', $mimeType);
            } else {
                $query->where('mime_type', 'like', "{$mimeType}/%");
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate(24);
    }

    public function findById(int $id): ?Media
    {
        return Media::find($id);
    }

    public function findByIds(array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        return Media::query()
            ->whereIn('id', $ids)
            ->get();
    }

    public function create(MediaForCreate $dto): Media
    {
        $disk = config('cms.media.disk', config('filesystems.default', 'public'));
        $path = $dto->file->store(config('cms.media.path', 'cms/media'), $disk);

        return Media::create([
            'name' => $dto->name ?? $dto->file->getClientOriginalName(),
            'file_name' => $dto->file->getClientOriginalName(),
            'mime_type' => $dto->file->getMimeType(),
            'size' => $dto->file->getSize(),
            'disk' => $disk,
            'path' => $path,
            'alt_text' => $dto->altText,
            'description' => $dto->description,
        ]);
    }

    public function update(int $id, MediaForUpdate $dto): Media
    {
        $media = Media::findOrFail($id);

        $updateData = [];

        if ($dto->altText !== null) {
            $updateData['alt_text'] = $dto->altText;
        }

        if ($dto->description !== null) {
            $updateData['description'] = $dto->description;
        }

        if (!empty($updateData)) {
            $media->update($updateData);
        }

        return $media;
    }

    public function delete(int $id): bool
    {
        $media = Media::findOrFail($id);

        // Удаляем файл с диска
        if (Storage::disk($media->disk)->exists($media->path)) {
            Storage::disk($media->disk)->delete($media->path);
        }

        return $media->delete();
    }
}
