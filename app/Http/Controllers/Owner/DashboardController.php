<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurants()
            ->with(['category', 'area', 'images', 'openingHours'])
            ->first();

        return view('owner.dashboard', compact('restaurant'));
    }
}
