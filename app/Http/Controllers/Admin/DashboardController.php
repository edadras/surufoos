<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Order;
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
            'recentMessages' => Contact::latest()->limit(5)->get(),
            'recentOrders' => settings()->commerceEnabled() ? Order::latest()->limit(5)->get() : collect(),
        ]);
    }
}
