<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Services\TasteFinderService;
use Illuminate\Http\Request;

class TasteFinderController extends Controller
{
    public function __invoke(Request $request, TasteFinderService $finder)
    {
        $data = $request->validate(['values' => ['required', 'array', 'max:12'], 'values.*' => ['integer']]);
        $products = $finder->recommend($data['values']);

        return response()->json([
            'count' => $products->count(),
            'html' => view('partials.taste-results', compact('products'))->render(),
        ]);
    }
}
