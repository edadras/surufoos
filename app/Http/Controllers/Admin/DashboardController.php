<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContentHealthService;

class DashboardController extends Controller
{
    public function __invoke(ContentHealthService $health)
    {
        return view('admin.dashboard', [
            'stats' => $health->stats(),
            'issues' => $health->issues(),
            'popular' => $health->popularProducts(),
            'searchTerms' => $health->topSearchTerms(),
            'views' => $health->dailyViews(14),
            'recentMessages' => \App\Models\Contact::latest()->limit(5)->get(),
            'recentOrders' => settings()->commerceEnabled() ? \App\Models\Order::latest()->limit(5)->get() : collect(),
        ]);
    }
}
