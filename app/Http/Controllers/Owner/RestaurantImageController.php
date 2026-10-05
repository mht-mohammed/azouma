<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UploadRestaurantImagesRequest;
use App\Jobs\ProcessRestaurantImage;
use App\Models\Restaurant;
use App\Models\RestaurantImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RestaurantImageController extends Controller
{
    private const MAX_IMAGES = 10;

    private function owned(Request $request, Restaurant $restaurant): Restaurant
    {
        $restaurant = $request->user()->restaurants()->findOrFail($restaurant->getKey());
        Gate::authorize('update', $restaurant);

        return $restaurant;
    }

    public function store(
        UploadRestaurantImagesRequest $request,
        Restaurant $restaurant
    ): RedirectResponse {
        $restaurant = $this->owned($request, $restaurant);

        $files = $request->file('images');

        if ($restaurant->images()->count() + count($files) > self::MAX_IMAGES) {
            throw ValidationException::withMessages([
                'images' => 'الحد الأقصى '.self::MAX_IMAGES.' صور لكل مطعم.',
            ]);
        }

        DB::transaction(function () use ($restaurant, $files) {
            $nextOrder = (int) $restaurant->images()->max('sort_order') + 1;
            $hasCover = $restaurant->images()->where('is_cover', true)->exists();

            foreach ($files as $index => $file) {
                // Stored as-is; ProcessRestaurantImage optimizes it in the background.
                $path = $file->storeAs(
                    'restaurants/'.$restaurant->id,
                    'original-'.Str::uuid().'.'.$file->extension(),
                    'public'
                );

                $image = $restaurant->images()->create([
                    'path' => 'storage/'.$path,
                    'is_cover' => ! $hasCover && $index === 0,
                    'sort_order' => $nextOrder + $index,
                ]);

                ProcessRestaurantImage::dispatch($image->id);
            }
        });

        return back()->with('success', 'تم رفع الصور. ستظهر بعد تجهيزها.');
    }

    public function destroy(Request $request, Restaurant $restaurant, RestaurantImage $image): RedirectResponse
    {
        $restaurant = $this->owned($request, $restaurant);
        $image = $restaurant->images()->findOrFail($image->getKey());

        DB::transaction(function () use ($restaurant, $image) {
            Storage::disk('public')->delete($this->storedFiles($image));
            $wasCover = $image->is_cover;
            $image->delete();

            if ($wasCover) {
                $restaurant->images()->orderBy('sort_order')->first()
                    ?->update(['is_cover' => true]);
            }
        });

        return back()->with('success', 'تم حذف الصورة.');
    }

    public function cover(Request $request, Restaurant $restaurant, RestaurantImage $image): RedirectResponse
    {
        $restaurant = $this->owned($request, $restaurant);
        $image = $restaurant->images()->findOrFail($image->getKey());

        DB::transaction(function () use ($restaurant, $image) {
            $restaurant->images()->update(['is_cover' => false]);
            $image->update(['is_cover' => true]);
        });

        return back()->with('success', 'تم تعيين صورة الغلاف.');
    }

    public function move(Request $request, Restaurant $restaurant, RestaurantImage $image): RedirectResponse
    {
        $restaurant = $this->owned($request, $restaurant);
        $image = $restaurant->images()->findOrFail($image->getKey());

        $direction = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ])['direction'];

        $ordered = $restaurant->images()->orderBy('sort_order')->get();
        $position = $ordered->search(fn (RestaurantImage $item) => $item->is($image));
        $swapWith = $direction === 'up' ? $position - 1 : $position + 1;

        if ($position !== false && isset($ordered[$swapWith])) {
            DB::transaction(function () use ($ordered, $position, $swapWith) {
                $currentOrder = $ordered[$position]->sort_order;
                $ordered[$position]->update(['sort_order' => $ordered[$swapWith]->sort_order]);
                $ordered[$swapWith]->update(['sort_order' => $currentOrder]);
            });
        }

        return back();
    }

    /**
     * All files belonging to the image (original, optimized, thumbnail);
     * stored paths look like "storage/..." URLs, so strip the prefix.
     *
     * @return array<int, string>
     */
    private function storedFiles(RestaurantImage $image): array
    {
        $strip = fn (?string $path) => $path
            ? (string) preg_replace('#^storage/#', '', $path)
            : null;

        return array_values(array_filter([
            $strip($image->path),
            $strip($image->thumbnail_path),
        ]));
    }
}
