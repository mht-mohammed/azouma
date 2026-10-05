<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Enums\RestaurantStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Restaurant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'pendingCount' => Restaurant::where('status', RestaurantStatus::PENDING)->count(),
            'approvedCount' => Restaurant::where('status', RestaurantStatus::APPROVED)->count(),
            'rejectedCount' => Restaurant::where('status', RestaurantStatus::REJECTED)->count(),
            'newReportsCount' => Report::where('status', ReportStatus::NEW)->count(),
        ]);
    }
}
