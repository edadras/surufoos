<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\PageSection;
use App\Support\CacheGroups;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Controlled page builder: editors add, order, toggle and fill sections from
 * a fixed set of types. Each type has its own settings schema; layout and
 * styling always come from the design system.
 */
class PageSectionController extends Controller
{
    public function store(Request $request, Page $page)
    {
        $data = $request->validate(['type' => ['required', Rule::enum(SectionType::class)]]);
        $section = $page->sections()->create([
            'type' => $data['type'],
            'sort_order' => (int) $page->sections()->max('sort_order') + 1,
            'settings' => [],
        ]);

        return redirect()->route('admin.pages.sections.edit', [$page, $section]);
    }

    public function edit(Page $page, PageSection $section)
    {
        abort_unless($section->page_id === $page->id, 404);

        return view('admin.pages.section', [
            'page' => $page,
            'section' => $section->load('translations'),
            'schema' => $section->type->settingsSchema(),
            'categories' => Category::with('translations')->orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Page $page, PageSection $section)
    {
        abort_unless($section->page_id === $page->id, 404);
        $rules = ['is_active' => ['boolean'], 'settings' => ['nullable', 'array']];
        foreach (Locales::codes() as $l) {
            $rules += [
                "translations.$l.eyebrow" => ['nullable', 'string', 'max:191'],
                "translations.$l.title" => ['nullable', 'string', 'max:191'],
                "translations.$l.subtitle" => ['nullable', 'string', 'max:2000'],
                "translations.$l.body" => ['nullable', 'string', 'max:100000'],
                "translations.$l.cta_label" => ['nullable', 'string', 'max:191'],
                "translations.$l.cta_url" => ['nullable', 'string', 'max:500'],
                "translations.$l.items" => ['nullable', 'string', 'max:2000'],
            ];
        }
        foreach ($section->type->settingsSchema() as $key => $type) {
            $rules["settings.$key"] = match ($type) {
                'number' => ['nullable', 'integer', 'min:1', 'max:48'],
                'media', 'category' => ['nullable', 'integer'],
                'media_multi' => ['nullable', 'array'],
                'boolean' => ['nullable', 'boolean'],
                'product_source' => ['nullable', Rule::in(['featured', 'new', 'popular', 'category'])],
                default => ['nullable', 'string', 'max:500'],
            };
        }
        $data = $request->validate($rules);

        DB::transaction(function () use ($section, $data, $request) {
            $section->update(['is_active' => $request->boolean('is_active'), 'settings' => $data['settings'] ?? []]);
            foreach (Locales::codes() as $locale) {
                $t = $data['translations'][$locale] ?? [];
                // Story keywords / list items: one per line.
                $t['items'] = isset($t['items']) && filled($t['items'])
                    ? collect(preg_split('/\R/u', $t['items']))->map('trim')->filter()->map(fn ($text) => ['text' => $text])->values()->all()
                    : null;
                if (collect($t)->filter(fn ($v) => filled($v))->isEmpty()) {
                    $section->translations()->where('locale', $locale)->delete();

                    continue;
                }
                $section->translations()->updateOrCreate(['locale' => $locale], $t);
            }
        });
        CacheGroups::flush(CacheGroups::HOME, CacheGroups::CONTENT);

        return back()->with('success', 'سکشن ذخیره شد.');
    }

    public function destroy(Page $page, PageSection $section)
    {
        abort_unless($section->page_id === $page->id, 404);
        $section->delete();

        return redirect()->route('admin.pages.edit', $page)->with('success', 'سکشن حذف شد.');
    }

    public function toggle(Page $page, PageSection $section)
    {
        abort_unless($section->page_id === $page->id, 404);
        $section->update(['is_active' => ! $section->is_active]);

        return back();
    }

    public function reorder(Request $request, Page $page)
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
        DB::transaction(fn () => collect($ids)->each(fn ($id, $i) => $page->sections()->whereKey($id)->update(['sort_order' => $i])));
        CacheGroups::flush(CacheGroups::HOME, CacheGroups::CONTENT);

        return response()->json(['ok' => true]);
    }
}
