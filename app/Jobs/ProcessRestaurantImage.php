<?php

namespace App\Jobs;

use App\Models\RestaurantImage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;

class ProcessRestaurantImage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $imageId) {}

    public function handle(): void
    {
        $image = RestaurantImage::find($this->imageId);

        if (! $image) {
            return;
        }

        $sourceRelative = $this->relativePath($image->path);
        $sourceAbsolute = Storage::disk('public')->path($sourceRelative);

        if (! is_file($sourceAbsolute)) {
            return;
        }

        $directory = dirname($sourceRelative);
        $name = (string) preg_replace(
            '/^original-/',
            '',
            pathinfo($sourceRelative, PATHINFO_FILENAME)
        );

        $optimizedRelative = $directory.'/'.$name.'.webp';
        $thumbnailRelative = $directory.'/'.$name.'-thumb.webp';

        $manager = new ImageManager(new GdDriver);

        // Re-runs overwrite the same outputs, so retries are idempotent.
        $optimized = $manager->read($sourceAbsolute)->scaleDown(width: 1600);
        $optimized->toWebp(quality: 80)->save(
            Storage::disk('public')->path($optimizedRelative)
        );

        $manager->read($sourceAbsolute)->scaleDown(width: 400)
            ->toWebp(quality: 75)
            ->save(Storage::disk('public')->path($thumbnailRelative));

        $image->update([
            'path' => 'storage/'.$optimizedRelative,
            'thumbnail_path' => 'storage/'.$thumbnailRelative,
            'width' => $optimized->width(),
            'height' => $optimized->height(),
        ]);

        if ($optimizedRelative !== $sourceRelative) {
            Storage::disk('public')->delete($sourceRelative);
        }
    }

    private function relativePath(string $path): string
    {
        return (string) preg_replace('#^storage/#', '', $path);
    }
}
