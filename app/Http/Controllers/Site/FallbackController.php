<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class FallbackController extends Controller
{
    public function __invoke(Request $request)
    {
        $segment = $request->segment(1);
        $locale = Locales::isSupported($segment) ? $segment : Locales::source();
        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        return response()->view('errors.404', [], 404);
    }
}
