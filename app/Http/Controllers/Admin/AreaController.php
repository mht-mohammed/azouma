<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AreaRequest;
use App\Models\Area;
use App\Support\ArabicSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(): View
    {
        $areas = Area::withCount('restaurants')->orderBy('name_ar')->get();

        return view('admin.areas.index', compact('areas'));
    }

    public function store(AreaRequest $request): RedirectResponse
    {
        $name = $request->validated('name_ar');

        Area::create([
            'name_ar' => $name,
            'slug' => ArabicSlug::uniqueSlug(
                $name,
                fn (string $slug) => Area::where('slug', $slug)->exists()
            ),
        ]);

        return back()->with('success', 'تمت إضافة المنطقة.');
    }

    public function update(AreaRequest $request, Area $area): RedirectResponse
    {
        $name = $request->validated('name_ar');

        $area->update([
            'name_ar' => $name,
            'slug' => ArabicSlug::uniqueSlug(
                $name,
                fn (string $slug) => Area::where('slug', $slug)
                    ->where('id', '!=', $area->id)
                    ->exists()
            ),
        ]);

        return back()->with('success', 'تم حفظ المنطقة.');
    }

    public function destroy(Area $area): RedirectResponse
    {
        if ($area->restaurants()->exists()) {
            return back()->with('error', 'لا يمكن حذف منطقة تحتوي على مطاعم.');
        }

        $area->delete();

        return back()->with('success', 'تم حذف المنطقة.');
    }
}
