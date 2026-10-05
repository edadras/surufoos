<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PageView;
use App\Models\SearchTerm;
use App\Services\ContentHealthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request, ContentHealthService $health)
    {
        $days = in_array($request->integer('days'), [7, 30, 90], true) ? $request->integer('days') : 30;
        $since = today()->subDays($days - 1);

        return view('admin.reports.index', [
            'days' => $days,
            'views' => $health->dailyViews($days),
            'topPages' => PageView::where('date', '>=', $since)->groupBy('path')->select('path', DB::raw('SUM(views) as total'))->orderByDesc('total')->limit(20)->get(),
            'byLocale' => PageView::where('date', '>=', $since)->groupBy('locale')->select('locale', DB::raw('SUM(views) as total'))->pluck('total', 'locale'),
            'popular' => $health->popularProducts(15),
            'searchTerms' => SearchTerm::orderByDesc('hits')->limit(30)->get(),
            'zeroResults' => SearchTerm::where('last_results_count', 0)->orderByDesc('hits')->limit(20)->get(),
        ]);
    }

    public function activity(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->query('action')))
            ->when($request->integer('user'), fn ($q, $u) => $q->where('user_id', $u))
            ->latest()->paginate(50)->withQueryString();

        return view('admin.reports.activity', compact('logs'));
    }
}
