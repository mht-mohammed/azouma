<?php

namespace Tests\Feature\Images;

use App\Jobs\ProcessRestaurantImage;
use App\Models\Restaurant;
use App\Models\RestaurantImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageProcessingTest extends TestCase
{
    use RefreshDatabase;

    private function storedUpload(int $restaurantId, int $width = 1200, int $height = 800): RestaurantImage
    {
        $file = UploadedFile::fake()->image('photo.jpg', $width, $height);
        $relative = 'restaurants/'.$restaurantId.'/original-test.jpg';
        Storage::disk('public')->put($relative, $file->getContent());

        return RestaurantImage::factory()->create([
            'restaurant_id' => $restaurantId,
            'path' => 'storage/'.$relative,
        ]);
    }

    public function test_job_creates_optimized_webp_and_thumbnail(): void
    {
        config()->set('queue.default', 'sync');
        Storage::fake('public');
        $restaurant = Restaurant::factory()->approved()->create();

        $image = $this->storedUpload($restaurant->id, 2000, 1000);
        ProcessRestaurantImage::dispatch($image->id);

        $image->refresh();
        $this->assertStringEndsWith('.webp', $image->path);
        $this->assertStringEndsWith('-thumb.webp', $image->thumbnail_path);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $image->path));
        Storage::disk('public')->assertExists(str_replace('storage/', '', $image->thumbnail_path));
        Storage::disk('public')->assertMissing('restaurants/'.$restaurant->id.'/original-test.jpg');
        // Resized to max width 1600, aspect kept.
        $this->assertSame(1600, $image->width);
        $this->assertSame(800, $image->height);
    }

    public function test_small_images_are_never_upscaled(): void
    {
        config()->set('queue.default', 'sync');
        Storage::fake('public');
        $restaurant = Restaurant::factory()->approved()->create();

        $image = $this->storedUpload($restaurant->id, 300, 200);
        ProcessRestaurantImage::dispatch($image->id);

        $image->refresh();
        $this->assertSame(300, $image->width);
        $this->assertSame(200, $image->height);
    }

    public function test_job_is_safe_when_record_or_file_is_missing(): void
    {
        Storage::fake('public');

        // Deleted record: nothing to do, no exception.
        ProcessRestaurantImage::dispatch(999999);

        // Missing file: record untouched, no exception.
        $restaurant = Restaurant::factory()->approved()->create();
        $image = RestaurantImage::factory()->create([
            'restaurant_id' => $restaurant->id,
            'path' => 'storage/restaurants/'.$restaurant->id.'/gone.jpg',
        ]);

        ProcessRestaurantImage::dispatch($image->id);

        $this->assertNull($image->fresh()->thumbnail_path);
        $this->assertNull($image->fresh()->width);

        $this->assertTrue(true);
    }
}
