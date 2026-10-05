<?php

namespace App\Http\Controllers;

use App\Enums\ReportStatus;
use App\Http\Requests\StoreReportRequest;
use App\Models\Restaurant;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public function store(StoreReportRequest $request, Restaurant $restaurant): RedirectResponse
    {
        $restaurant->reports()->create(array_merge(
            $request->safe()->except('website'),
            ['status' => ReportStatus::NEW]
        ));

        return back()->with('success', 'شكراً لك! وصل بلاغك وسنراجع المعلومات.');
    }
}
