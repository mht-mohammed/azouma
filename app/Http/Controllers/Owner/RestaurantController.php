<?php

namespace App\Http\Controllers\Owner;

use App\Actions\UpdateRestaurantAction;
use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Enums\Weekday;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreRestaurantRequest;
use App\Http\Requests\Owner\UpdateOperatingStatusRequest;
use App\Http\Requests\Owner\UpdateRestaurantRequest;
use App\Models\Area;
use App\Models\Category;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\RestaurantSubmittedForReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    /**
     * Always scope to the authenticated owner: other owners'
     * restaurants 404 here before any policy is even checked.
     */
    private function owned(Request $request, Restaurant $restaurant): Restaurant
    {
        return $request->user()->restaurants()->findOrFail($restaurant->getKey());
    }

    public function create(): View
    {
        Gate::authorize('create', Restaurant::class);

        return view('owner.restaurants.create', [
            'categories' => Category::orderedList(),
            'areas' => Area::orderedList(),
        ]);
    }

    public function store(StoreRestaurantRequest $request): RedirectResponse
    {
        $restaurant = $request->user()->restaurants()->create(array_merge(
            $request->validated(),
            [
                'status' => RestaurantStatus::PENDING,
                'operating_status_updated_at' => now(),
            ]
        ));

        $this->notifyAdmins($restaurant);

        return redirect()
            ->route('owner.restaurants.edit', $restaurant)
            ->with('success', 'تم إنشاء مطعمك وهو الآن قيد المراجعة. أكمل الساعات والصور.');
    }

    public function edit(Request $request, Restaurant $restaurant): View
    {
        $restaurant = $this->owned($request, $restaurant);
        Gate::authorize('update', $restaurant);

        $restaurant->loadMissing(['images', 'openingHours']);

        return view('owner.restaurants.edit', [
            'restaurant' => $restaurant,
            'categories' => Category::orderedList(),
            'areas' => Area::orderedList(),
            'weekdays' => Weekday::cases(),
            'hoursByDay' => $restaurant->openingHours->keyBy(
                fn ($hour) => $hour->day_of_week->value
            ),
        ]);
    }

    public function update(
        UpdateRestaurantRequest $request,
        Restaurant $restaurant,
        UpdateRestaurantAction $action
    ): RedirectResponse {
        $restaurant = $this->owned($request, $restaurant);

        $sentBack = $action->execute($restaurant, $request->validated());

        if ($sentBack) {
            $this->notifyAdmins($restaurant->fresh());
        }

        return redirect()
            ->route('owner.dashboard')
            ->with('success', $sentBack
                ? 'تم حفظ التعديلات. لأنك غيّرت بيانات أساسية، عاد المطعم إلى قيد المراجعة.'
                : 'تم حفظ التعديلات.');
    }

    public function updateStatus(
        UpdateOperatingStatusRequest $request,
        Restaurant $restaurant
    ): RedirectResponse {
        $restaurant = $this->owned($request, $restaurant);

        $restaurant->update([
            'operating_status' => $request->validated('operating_status'),
            'operating_status_updated_at' => now(),
        ]);

        return redirect()
            ->route('owner.dashboard')
            ->with('success', 'تم تحديث حالة الدوام.');
    }

    private function notifyAdmins(Restaurant $restaurant): void
    {
        $admins = User::where('role', UserRole::ADMIN)->get();

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new RestaurantSubmittedForReview($restaurant));
        }
    }
}
