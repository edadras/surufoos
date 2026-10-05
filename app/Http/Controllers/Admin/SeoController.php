<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use App\Support\Locales;
use Illuminate\Http\Request;

/** Meta tags for static listing routes (home, products, magazine…); entities carry their own SEO fields. */
class SeoController extends Controller
{
    public function index()
    {
        $rows = SeoMeta::whereNotNull('route_key')->get()->groupBy('route_key')->map->keyBy('locale');

        return view('admin.seo.index', ['routes' => SeoMeta::ROUTES, 'rows' => $rows]);
    }

    public function update(Request $request)
    {
        $rules = [];
        foreach (array_keys(SeoMeta::ROUTES) as $route) {
            foreach (Locales::codes() as $l) {
                $key = str_replace('.', '_', $route);
                $rules["meta.$key.$l.title"] = ['nullable', 'string', 'max:191'];
                $rules["meta.$key.$l.description"] = ['nullable', 'string', 'max:500'];
                $rules["meta.$key.$l.og_media_id"] = ['nullable', 'integer', 'exists:media,id'];
                $rules["meta.$key.$l.noindex"] = ['nullable', 'boolean'];
            }
        }
        $data = $request->validate($rules)['meta'] ?? [];

        foreach (array_keys(SeoMeta::ROUTES) as $route) {
            foreach (Locales::codes() as $l) {
                $row = $data[str_replace('.', '_', $route)][$l] ?? [];
                $values = ['title' => $row['title'] ?? null, 'description' => $row['description'] ?? null, 'og_media_id' => $row['og_media_id'] ?? null, 'noindex' => (bool) ($row['noindex'] ?? false)];
                if (! array_filter($values)) {
                    SeoMeta::where('route_key', $route)->where('locale', $l)->delete();

                    continue;
                }
                SeoMeta::updateOrCreate(['route_key' => $route, 'locale' => $l], $values);
            }
        }

        return back()->with('success', 'تنظیمات سئو ذخیره شد.');
    }
}
