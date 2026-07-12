<?php

declare(strict_types=1);

namespace Safarjaisur\AdminPanel\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;

/**
 * Generates the small/medium/large thumbnail variants configured under
 * sjadminpanel.media.thumbnails using Intervention Image, and keeps
 * them in sync (created on upload, removed on delete).
 *
 * Variants are stored next to the original as "{name}-{size}.{ext}",
 * e.g. "photo.jpg" -> "photo-small.jpg", "photo-medium.jpg", ...
 */
class MediaThumbnailService
{
    protected const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct(protected ImageManager $manager)
    {
    }

    public function isImage(string $path): bool
    {
        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true);
    }

    /**
     * @return array<string, string> size label => generated path
     */
    public function generate(Filesystem $disk, string $path): array
    {
        if (! $this->isImage($path)) {
            return [];
        }

        $sizes = config('sjadminpanel.media.thumbnails', []);
        $generated = [];

        try {
            $original = $this->manager->read($disk->get($path));
        } catch (\Throwable $exception) {
            Log::warning('sjadminpanel: could not read image for thumbnailing', [
                'path' => $path,
                'message' => $exception->getMessage(),
            ]);

            return [];
        }

        foreach ($sizes as $label => $dimensions) {
            [$width, $height] = array_pad((array) $dimensions, 2, null);

            $thumbnail = clone $original;
            $thumbnail->cover((int) $width, (int) ($height ?? $width));

            $thumbnailPath = $this->thumbnailPath($path, (string) $label);
            $disk->put($thumbnailPath, (string) $thumbnail->encode());
            $generated[$label] = $thumbnailPath;
        }

        return $generated;
    }

    public function delete(Filesystem $disk, string $path): void
    {
        foreach (array_keys(config('sjadminpanel.media.thumbnails', [])) as $label) {
            $thumbnailPath = $this->thumbnailPath($path, (string) $label);

            if ($disk->exists($thumbnailPath)) {
                $disk->delete($thumbnailPath);
            }
        }
    }

    public function thumbnailPath(string $path, string $label): string
    {
        $directory = pathinfo($path, PATHINFO_DIRNAME);
        $filename = pathinfo($path, PATHINFO_FILENAME);
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $thumbName = "{$filename}-{$label}." . ($extension ?: 'jpg');

        return $directory === '.' ? $thumbName : Str::finish($directory, '/') . $thumbName;
    }
}
