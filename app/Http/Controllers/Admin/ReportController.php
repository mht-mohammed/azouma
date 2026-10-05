<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:new,resolved'],
        ]);

        $reports = Report::query()
            ->when(isset($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->with('restaurant')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'filters' => array_merge(['status' => ''], $filters),
        ]);
    }

    public function show(Report $report): View
    {
        $report->load('restaurant.owner');

        return view('admin.reports.show', compact('report'));
    }

    public function resolve(Report $report): RedirectResponse
    {
        $report->update(['status' => ReportStatus::RESOLVED]);

        return redirect()
            ->route('admin.reports.index')
            ->with('success', 'تم وضع البلاغ كمعالَج.');
    }
}
