<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Slider;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SliderController extends Controller
{
    public function index()
    {
        return view('admin.sliders.index', ['sliders' => Slider::with(['translations', 'media', 'product.translations'])->orderBy('placement')->orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.sliders.form', ['slider' => new Slider(['placement' => 'home_hero', 'is_active' => true]), 'products' => $this->products()]);
    }

    public function store(Request $request)
    {
        $slider = $this->save(new Slider, $request);

        return redirect()->route('admin.sliders.edit', $slider)->with('success', 'اسلاید ذخیره شد.');
    }

    public function edit(Slider $slider)
    {
        return view('admin.sliders.form', ['slider' => $slider->load('translations'), 'products' => $this->products()]);
    }

    public function update(Request $request, Slider $slider)
    {
        $this->save($slider, $request);

        return back()->with('success', 'تغییرات ذخیره شد.');
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();

        return redirect()->route('admin.sliders.index')->with('success', 'حذف شد.');
    }

    private function save(Slider $slider, Request $request): Slider
    {
        $rules = [
            'placement' => ['required', 'string', 'max:32', 'regex:/^[a-z0-9_]+$/'],
            'media_id' => ['nullable', 'integer', 'exists:media,id'],
            'mobile_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'background_color' => ['nullable', 'hex_color'],
            'sort_order' => ['nullable', 'integer'],
            'layers' => ['nullable', 'array', 'max:12'],
            'layers.*.media_id' => ['required', 'integer', 'exists:media,id'],
            'layers.*.x' => ['required', 'numeric', 'between:0,100'],
            'layers.*.y' => ['required', 'numeric', 'between:0,100'],
            'layers.*.depth' => ['required', 'numeric', 'between:0,1'],
            'layers.*.scale' => ['nullable', 'numeric', 'between:0.2,3'],
            'layers.*.rotate' => ['nullable', 'numeric', 'between:-180,180'],
        ];
        foreach (Locales::codes() as $l) {
            foreach (['eyebrow', 'title', 'cta_label', 'cta2_label'] as $f) {
                $rules["translations.$l.$f"] = ['nullable', 'string', 'max:191'];
            }
            $rules["translations.$l.subtitle"] = ['nullable', 'string', 'max:1000'];
            $rules["translations.$l.cta_url"] = ['nullable', 'string', 'max:500'];
            $rules["translations.$l.cta2_url"] = ['nullable', 'string', 'max:500'];
        }
        $rules['translations.fa.title'] = ['required', 'string', 'max:191'];
        $data = $request->validate($rules);

        return DB::transaction(function () use ($slider, $data, $request) {
            $slider->fill(collect($data)->only(['placement', 'media_id', 'mobile_media_id', 'product_id', 'background_color'])->all());
            $slider->sort_order = (int) ($data['sort_order'] ?? 0);
            $slider->is_active = $request->boolean('is_active');
            $slider->layers = array_values($data['layers'] ?? []);
            $slider->save();
            $slider->saveTranslations($data['translations'] ?? []);

            return $slider;
        });
    }

    private function products()
    {
        return Product::with('translations')->orderByDesc('id')->get();
    }
}
