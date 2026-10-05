<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Support\Locales;
use Illuminate\Http\Request;

class LocaleRedirectController extends Controller
{
    public function __invoke(Request $request)
    {
        $preferred = $request->getPreferredLanguage(Locales::codes()) ?: Locales::source();
        $locale = Locales::isSupported(substr($preferred, 0, 2)) ? substr($preferred, 0, 2) : Locales::source();

        return redirect()->route('home', ['locale' => $locale], 302);
    }
}
