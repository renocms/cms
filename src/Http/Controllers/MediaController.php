<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Reno\Cms\DTO\Media\MediaFilters;
use Reno\Cms\DTO\Media\MediaForCreate;
use Reno\Cms\DTO\Media\MediaForUpdate;
use Reno\Cms\Http\Requests\Media\MediaIndexRequest;
use Reno\Cms\Http\Requests\Media\MediaStoreRequest;
use Reno\Cms\Http\Requests\Media\MediaThumbnailsRequest;
use Reno\Cms\Http\Requests\Media\MediaUpdateRequest;
use Reno\Cms\Http\Resources\Media\MediaResource;
use Reno\Cms\Interfaces\Services\MediaServiceInterface;
use Reno\Cms\Interfaces\Services\MediaThumbnailServiceInterface;

class MediaController extends Controller
{
    public function __construct(
        protected MediaServiceInterface $mediaService,
        protected MediaThumbnailServiceInterface $mediaThumbnailService,
    )
    {
    }

    public function index(MediaIndexRequest $request): JsonResponse
    {
        $dto = MediaFilters::createFromRequest($request);

        $media = $this->mediaService->getAll($dto);

        return MediaResource::collection($media)->response();
    }

    public function show(int $id): JsonResponse
    {
        $media = $this->mediaService->findById($id);

        if (!$media) {
            return response()->json(['message' => __('cms::cms.media_not_found')], 404);
        }

        return (new MediaResource($media))->response();
    }

    public function store(MediaStoreRequest $request): JsonResponse
    {
        $dto = MediaForCreate::createFromRequest($request);

        $media = $this->mediaService->create($dto);

        return (new MediaResource($media))->response()->setStatusCode(201);
    }

    public function update(MediaUpdateRequest $request, int $id): JsonResponse
    {
        $dto = MediaForUpdate::createFromRequest($request);

        $media = $this->mediaService->update($id, $dto);

        return (new MediaResource($media))->response();
    }

    public function destroy(int $id): JsonResponse
    {
        $this->mediaService->delete($id);

        return response()->json(['message' => __('cms::cms.media_deleted')], 200);
    }

    public function thumbnails(MediaThumbnailsRequest $request): JsonResponse
    {
        $ids = collect($request->validated('ids', []))
            ->map(static fn (mixed $value) => (int) $value)
            ->filter(static fn (int $value) => $value > 0)
            ->unique()
            ->values()
            ->all();

        $width = (int) $request->validated('width', 80);
        $height = (int) $request->validated('height', 80);
        $options = $request->validated('options', 'zc=1');

        $mediaItems = $this->mediaService->findByIds($ids)
            ->keyBy('id');

        $result = collect($ids)
            ->mapWithKeys(function (int $id) use ($mediaItems, $width, $height, $options): array {
                $media = $mediaItems->get($id);
                $url = $media
                    ? $this->mediaThumbnailService->getThumbnailUrl($media, $width, $height, $options)
                    : null;

                return [(string) $id => $url];
            })
            ->all();

        return response()->json([
            'data' => $result,
        ]);
    }
}
