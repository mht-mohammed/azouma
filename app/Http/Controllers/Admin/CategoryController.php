<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Support\ArabicSlug;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::withCount('restaurants')->orderBy('name_ar')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $name = $request->validated('name_ar');

        Category::create([
            'name_ar' => $name,
            'slug' => ArabicSlug::uniqueSlug(
                $name,
                fn (string $slug) => Category::where('slug', $slug)->exists()
            ),
        ]);

        return back()->with('success', 'تمت إضافة التصنيف.');
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $name = $request->validated('name_ar');

        $category->update([
            'name_ar' => $name,
            'slug' => ArabicSlug::uniqueSlug(
                $name,
                fn (string $slug) => Category::where('slug', $slug)
                    ->where('id', '!=', $category->id)
                    ->exists()
            ),
        ]);

        return back()->with('success', 'تم حفظ التصنيف.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->restaurants()->exists()) {
            return back()->with('error', 'لا يمكن حذف تصنيف يحتوي على مطاعم.');
        }

        $category->delete();

        return back()->with('success', 'تم حذف التصنيف.');
    }
}
