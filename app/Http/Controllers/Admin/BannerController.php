<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index()
    {
        return view('admin.banners.index', ['banners' => Banner::with('desktopMedia')->orderBy('placement')->orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.banners.form', ['banner' => new Banner(['placement' => 'home', 'layout' => 'full', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        Banner::create($this->validated($request));

        return redirect()->route('admin.banners.index')->with('success', 'بنر ذخیره شد.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $banner->update($this->validated($request));

        return back()->with('success', 'تغییرات ذخیره شد.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'حذف شد.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'locale' => ['nullable', Rule::in(Locales::codes())],
            'placement' => ['required', 'string', 'max:32', 'regex:/^[a-z0-9_]+$/'],
            'layout' => ['required', Rule::in(['full', 'split'])],
            'desktop_media_id' => ['required', 'integer', 'exists:media,id'],
            'mobile_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'title' => ['nullable', 'string', 'max:191'],
            'subtitle' => ['nullable', 'string', 'max:1000'],
            'cta_label' => ['nullable', 'string', 'max:191'],
            'url' => ['nullable', 'string', 'max:500'],
            'color' => ['nullable', 'hex_color'],
            'text_color' => ['nullable', 'hex_color'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'sort_order' => ['nullable', 'integer'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
