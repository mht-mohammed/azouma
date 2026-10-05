<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApproveRestaurantAction;
use App\Actions\RejectRestaurantAction;
use App\Actions\UnverifyRestaurantAction;
use App\Actions\VerifyRestaurantAction;
use App\Enums\RestaurantStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectRestaurantRequest;
use App\Models\Restaurant;
use App\Notifications\RestaurantApproved;
use App\Notifications\RestaurantRejected;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function pending(): View
    {
        $restaurants = Restaurant::where('status', RestaurantStatus::PENDING)
            ->with(['owner', 'category', 'area', 'coverImage'])
            ->latest()
            ->paginate(12);

        return view('admin.restaurants.pending', compact('restaurants'));
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $restaurants = Restaurant::query()
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->search($filters['q'] ?? null)
            ->with(['owner', 'category', 'area'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.restaurants.index', [
            'restaurants' => $restaurants,
            'filters' => array_merge(['status' => '', 'q' => ''], $filters),
        ]);
    }

    public function show(Restaurant $restaurant): View
    {
        $restaurant->load(['owner', 'category', 'area', 'images', 'openingHours']);

        return view('admin.restaurants.show', compact('restaurant'));
    }

    public function approve(Restaurant $restaurant, ApproveRestaurantAction $action): RedirectResponse
    {
        $action->execute($restaurant);

        $restaurant->owner->notify(new RestaurantApproved($restaurant));

        return redirect()
            ->route('admin.restaurants.pending')
            ->with('success', 'تم اعتماد المطعم وإشعار صاحبه.');
    }

    public function reject(
        RejectRestaurantRequest $request,
        Restaurant $restaurant,
        RejectRestaurantAction $action
    ): RedirectResponse {
        $action->execute($restaurant, $request->validated('rejection_reason'));

        $restaurant->owner->notify(new RestaurantRejected($restaurant->fresh()));

        return redirect()
            ->route('admin.restaurants.pending')
            ->with('success', 'تم رفض المطعم وإشعار صاحبه بالسبب.');
    }

    public function verify(Restaurant $restaurant, VerifyRestaurantAction $action): RedirectResponse
    {
        $action->execute($restaurant);

        return back()->with('success', 'تم توثيق المطعم.');
    }

    public function unverify(Restaurant $restaurant, UnverifyRestaurantAction $action): RedirectResponse
    {
        $action->execute($restaurant);

        return back()->with('success', 'تمت إزالة التوثيق.');
    }
}
