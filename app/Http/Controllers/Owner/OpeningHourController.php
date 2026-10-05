<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdateOpeningHoursRequest;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OpeningHourController extends Controller
{
    public function update(
        UpdateOpeningHoursRequest $request,
        Restaurant $restaurant
    ): RedirectResponse {
        $restaurant = $request->user()->restaurants()->findOrFail($restaurant->getKey());
        Gate::authorize('update', $restaurant);

        DB::transaction(function () use ($restaurant, $request) {
            foreach ($request->validated('hours') as $row) {
                $closed = (bool) ($row['is_closed'] ?? false);

                $restaurant->openingHours()->updateOrCreate(
                    ['day_of_week' => $row['day_of_week']],
                    [
                        'opens_at' => $closed ? null : $row['opens_at'].':00',
                        'closes_at' => $closed ? null : $row['closes_at'].':00',
                        'is_closed' => $closed,
                    ]
                );
            }
        });

        return redirect()
            ->route('owner.dashboard')
            ->with('success', 'تم حفظ ساعات الدوام.');
    }
}
