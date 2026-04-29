<?php

namespace Reno\Cms\Interfaces\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Reno\Cms\DTO\Media\MediaFilters;
use Reno\Cms\DTO\Media\MediaForCreate;
use Reno\Cms\DTO\Media\MediaForUpdate;
use Reno\Cms\Models\Media;

interface MediaServiceInterface
{
    public function getAll(MediaFilters $dto): LengthAwarePaginator;

    public function findById(int $id): ?Media;

    public function findByIds(array $ids): Collection;

    public function create(MediaForCreate $dto): Media;

    public function update(int $id, MediaForUpdate $dto): Media;

    public function delete(int $id): bool;
}
