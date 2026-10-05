<?php

namespace App\Http\Controllers;

use App\Enums\RestaurantStatus;
use App\Http\Requests\RestaurantFilterRequest;
use App\Models\Area;
use App\Models\Category;
use App\Models\Restaurant;
use App\Queries\PublicRestaurantQuery;

class RestaurantController extends Controller
{
    public function index(RestaurantFilterRequest $request, PublicRestaurantQuery $query)
    {
        return view('restaurants.index', [
            'restaurants' => $query($request->validated()),
            'categories' => Category::orderBy('name_ar')->get(),
            'areas' => Area::orderBy('name_ar')->get(),
            'filters' => array_merge(
                ['category' => '', 'area' => '', 'q' => ''],
                $request->validated()
            ),
        ]);
    }

    public function show(Restaurant $restaurant)
    {
        abort_if($restaurant->status !== RestaurantStatus::APPROVED, 404);

        $restaurant->loadMissing(['category', 'area', 'images', 'openingHours']);

        return view('restaurants.show', [
            'restaurant' => $restaurant,
            // Carbon: Sunday=0 … Saturday=6. Ours: Saturday=0 … Friday=6.
            'today' => (now()->dayOfWeek + 1) % 7,
            'isOpenNow' => $restaurant->isOpenNow(),
        ]);
    }
}
