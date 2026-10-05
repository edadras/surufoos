<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\FavoriteService;
use App\Services\Seo;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function __construct(private FavoriteService $favorites) {}

    public function index(Seo $seo)
    {
        $products = Product::published()->translatedIn()->forCard()->whereIn('id', $this->favorites->ids())->get();
        $seo->set(__('ui.favorites.title'))->routeAlternates('favorites.index');
        $seo->noindex = true;

        return view('site.favorites', compact('products'));
    }

    public function toggle(Request $request, Product $product)
    {
        $active = $this->favorites->toggle($product);

        return $request->wantsJson()
            ? response()->json(['active' => $active, 'count' => count($this->favorites->ids())])
            : back();
    }
}
