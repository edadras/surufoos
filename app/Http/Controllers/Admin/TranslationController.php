<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContentHealthService;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * Translation manager: lists records of any translatable type with their
 * FA/EN/AR status and offers a side-by-side editor that only touches
 * translation rows — the screen the Translator role works in.
 */
class TranslationController extends Controller
{
    private const SKIP = ['id', 'locale', 'created_at', 'updated_at', 'search_text', 'items'];

    public function index(Request $request)
    {
        $type = $request->query('type', 'products');
        abort_unless(isset(ContentHealthService::TRANSLATABLE[$type]), 404);
        [$class, $label, $primary] = ContentHealthService::TRANSLATABLE[$type];
        $locale = $request->query('locale');

        $items = $class::with('translations')
            ->when(Locales::isSupported($locale), fn ($q) => $q->whereDoesntHave('translations', fn ($t) => $t->where('locale', $locale)))
            ->latest('id')->paginate(40)->withQueryString();

        $summary = [];
        foreach (ContentHealthService::TRANSLATABLE as $key => [$c, $l]) {
            $total = $c::count();
            $summary[$key] = ['label' => $l, 'total' => $total];
            foreach (Locales::codes() as $code) {
                $summary[$key][$code] = $total ? $c::whereHas('translations', fn ($t) => $t->where('locale', $code))->count() : 0;
            }
        }

        return view('admin.translations.index', compact('type', 'label', 'primary', 'items', 'summary', 'locale'));
    }

    public function edit(string $type, int $id)
    {
        [$class, $label, $primary] = $this->resolve($type);
        $model = $class::with('translations')->findOrFail($id);

        return view('admin.translations.edit', [
            'type' => $type, 'label' => $label, 'primary' => $primary, 'model' => $model,
            'fields' => $this->fields($model),
        ]);
    }

    public function update(Request $request, string $type, int $id)
    {
        [$class, , $primary] = $this->resolve($type);
        $model = $class::with('translations')->findOrFail($id);
        $fields = $this->fields($model);

        $rules = [];
        foreach (Locales::codes() as $l) {
            foreach ($fields as $f => $kind) {
                $rules["translations.$l.$f"] = ['nullable', 'string', $f === 'slug' ? 'regex:/^[\p{L}\p{N}\-]*$/u' : 'max:300000'];
            }
        }
        $data = $request->validate($rules)['translations'] ?? [];

        if ($type === 'media') {
            foreach ($data as $l => $t) {
                $t = array_filter($t, 'filled');
                $t ? $model->translations()->updateOrCreate(['locale' => $l], $t) : $model->translations()->where('locale', $l)->delete();
            }
        } else {
            // Never let a translator wipe the Persian source.
            if (blank($data[Locales::source()][$primary] ?? null)) {
                unset($data[Locales::source()]);
            }
            $model->saveTranslations($data, $primary);
        }

        return back()->with('success', 'ترجمه‌ها ذخیره شد.');
    }

    private function resolve(string $type): array
    {
        abort_unless(isset(ContentHealthService::TRANSLATABLE[$type]), 404);

        return ContentHealthService::TRANSLATABLE[$type];
    }

    /** @return array<string, string> column => input kind */
    private function fields($model): array
    {
        $table = $model->translationTable();
        $out = [];
        foreach (Schema::getColumns($table) as $col) {
            $name = $col['name'];
            if (in_array($name, self::SKIP, true) || $name === $model->translationForeignKey()) {
                continue;
            }
            $type = strtolower($col['type_name'] ?? $col['type']);
            $out[$name] = str_contains($type, 'text') ? (in_array($name, ['description', 'story', 'body', 'answer'], true) ? 'rich' : 'textarea') : 'text';
        }

        return $out;
    }
}
