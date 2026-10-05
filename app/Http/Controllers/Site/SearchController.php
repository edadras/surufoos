<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\SearchService;
use App\Services\Seo;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private SearchService $search) {}

    public function index(Request $request, Seo $seo)
    {
        $term = mb_substr(trim((string) $request->query('q')), 0, 100);
        $results = $term !== '' ? $this->search->search($term, 24) : null;
        if ($results) {
            $this->search->record($term, $results['total']);
        }

        $seo->set(__('ui.search.title'))->forRoute('search')->routeAlternates('search');
        $seo->noindex = true;
        $seo->breadcrumb(__('ui.search.title'));

        return view('site.search', compact('term', 'results'));
    }

    public function live(Request $request)
    {
        $term = mb_substr(trim((string) $request->query('q')), 0, 100);
        if (mb_strlen($term) < 2) {
            return response()->json(['html' => '', 'total' => 0]);
        }

        $results = $this->search->search($term, 5);
        $this->search->record($term, $results['total']);

        return response()->json([
            'html' => view('partials.search-results', compact('results', 'term'))->render(),
            'total' => $results['total'],
        ]);
    }
}
