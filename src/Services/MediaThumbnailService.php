<?php

namespace Reno\Cms\Services;

use Illuminate\Support\Facades\File;
use Reno\Cms\Interfaces\Services\MediaThumbnailServiceInterface;
use Reno\Cms\Models\Media;

class MediaThumbnailService implements MediaThumbnailServiceInterface
{
    public function getThumbnailUrl(Media $media, int $width = 80, int $height = 80, ?string $options = 'zc=1'): string
    {
        if (!str_starts_with($media->mime_type, 'image/')) {
            return $media->makeUrl();
        }

        if (!extension_loaded('gd')) {
            return $media->makeUrl();
        }

        $sourcePath = $media->makePath();
        if (!is_file($sourcePath) || !is_readable($sourcePath)) {
            return $media->makeUrl();
        }

        $parsedOptions = $this->parseOptions($options);
        $extension = $this->resolveExtension($media, $sourcePath);
        $relativePath = $this->buildRelativePath($media, $width, $height, $parsedOptions, $extension);
        $absolutePath = public_path($relativePath);

        if (is_file($absolutePath)) {
            return asset($relativePath);
        }

        $sourceImage = $this->createImageResource($sourcePath);
        if ($sourceImage === null) {
            return $media->makeUrl();
        }

        $targetImage = $this->buildTargetImage($sourceImage, $width, $height, $parsedOptions);
        if ($targetImage === null) {
            imagedestroy($sourceImage);
            return $media->makeUrl();
        }

        File::ensureDirectoryExists(dirname($absolutePath));
        $isSaved = $this->saveImageResource($targetImage, $absolutePath, $extension);

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        if (!$isSaved) {
            return $media->makeUrl();
        }

        return asset($relativePath);
    }

    private function parseOptions(?string $options): array
    {
        $result = [];
        if ($options === null || trim($options) === '') {
            return $result;
        }

        foreach (explode(',', $options) as $option) {
            $option = trim($option);
            if ($option === '' || !str_contains($option, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $option, 2);
            $result[trim($key)] = trim($value);
        }

        return $result;
    }

    private function resolveExtension(Media $media, string $sourcePath): string
    {
        $mimeType = strtolower($media->mime_type);
        return match ($mimeType) {
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => function_exists('imagewebp') ? 'webp' : 'jpg',
            default => $this->resolveFallbackExtension($sourcePath),
        };
    }

    private function resolveFallbackExtension(string $sourcePath): string
    {
        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));
        if (in_array($extension, ['png', 'gif', 'jpg', 'jpeg', 'webp'], true)) {
            return $extension === 'jpeg' ? 'jpg' : $extension;
        }

        return 'jpg';
    }

    private function buildRelativePath(Media $media, int $width, int $height, array $options, string $extension): string
    {
        $signature = md5(json_encode([
            'media_id' => $media->id,
            'updated_at' => $media->updated_at?->timestamp,
            'path' => $media->path,
            'width' => $width,
            'height' => $height,
            'options' => $options,
        ]));

        return "vendor/reno/cms/thumbs/{$signature}.{$extension}";
    }

    private function createImageResource(string $path): mixed
    {
        $content = @file_get_contents($path);
        if ($content === false) {
            return null;
        }

        return @imagecreatefromstring($content) ?: null;
    }

    private function buildTargetImage(mixed $sourceImage, int $width, int $height, array $options): mixed
    {
        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);
        if ($sourceWidth < 1 || $sourceHeight < 1 || $width < 1 || $height < 1) {
            return null;
        }

        $targetImage = imagecreatetruecolor($width, $height);
        if ($targetImage === false) {
            return null;
        }

        $this->enableTransparency($targetImage);
        $isZoomCrop = ($options['zc'] ?? null) === '1';

        if ($isZoomCrop) {
            $sourceRatio = $sourceWidth / $sourceHeight;
            $targetRatio = $width / $height;

            if ($sourceRatio > $targetRatio) {
                $cropHeight = $sourceHeight;
                $cropWidth = (int) round($sourceHeight * $targetRatio);
                $cropX = (int) round(($sourceWidth - $cropWidth) / 2);
                $cropY = 0;
            } else {
                $cropWidth = $sourceWidth;
                $cropHeight = (int) round($sourceWidth / $targetRatio);
                $cropX = 0;
                $cropY = (int) round(($sourceHeight - $cropHeight) / 2);
            }

            imagecopyresampled(
                $targetImage,
                $sourceImage,
                0,
                0,
                $cropX,
                $cropY,
                $width,
                $height,
                $cropWidth,
                $cropHeight,
            );

            return $targetImage;
        }

        $scale = min($width / $sourceWidth, $height / $sourceHeight);
        $scaledWidth = max((int) round($sourceWidth * $scale), 1);
        $scaledHeight = max((int) round($sourceHeight * $scale), 1);
        $offsetX = (int) floor(($width - $scaledWidth) / 2);
        $offsetY = (int) floor(($height - $scaledHeight) / 2);

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            $offsetX,
            $offsetY,
            0,
            0,
            $scaledWidth,
            $scaledHeight,
            $sourceWidth,
            $sourceHeight,
        );

        return $targetImage;
    }

    private function enableTransparency(mixed $image): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $transparent);
    }

    private function saveImageResource(mixed $image, string $path, string $extension): bool
    {
        return match ($extension) {
            'png' => imagepng($image, $path, 6),
            'gif' => imagegif($image, $path),
            'webp' => function_exists('imagewebp') ? imagewebp($image, $path, 85) : imagejpeg($image, $path, 85),
            default => imagejpeg($image, $path, 85),
        };
    }
}
