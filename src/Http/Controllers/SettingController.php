<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Reno\Cms\Http\Requests\Settings\SettingsIndexRequest;
use Reno\Cms\Http\Requests\Settings\SettingsUpdateRequest;
use Reno\Cms\Http\Resources\Settings\SettingResource;
use Reno\Cms\Interfaces\Repositories\SettingRepositoryInterface;

class SettingController extends Controller
{
    public function __construct(
        protected SettingRepositoryInterface $settingRepository
    )
    {
    }

    public function index(SettingsIndexRequest $request): JsonResponse
    {
        $contextId = $request->input('context_id');

        $settings = $this->settingRepository->getByContext($contextId);

        return SettingResource::collection($settings)->response();
    }

    public function updateMany(SettingsUpdateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $settings = $this->settingRepository->updateMany(
            $data['context_id'],
            $data['settings']
        );

        $result = $settings->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->getTypedValue()];
        });

        return response()->json(['data' => $result]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->settingRepository->delete($id);

        if (!$deleted) {
            return response()->json(['message' => __('cms::cms.setting_not_found')], 404);
        }

        return response()->json(['message' => __('cms::cms.setting_deleted')], 200);
    }
}

