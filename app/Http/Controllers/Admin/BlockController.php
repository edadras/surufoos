<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Faq;
use App\Models\Testimonial;
use App\Models\TimelineEntry;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Small translatable content blocks that share one list/form UI:
 * FAQs, testimonials, timeline / product-journey entries and certificates.
 * Each type declares its translatable fields and plain fields.
 */
class BlockController extends Controller
{
    public const TYPES = [
        'faqs' => [
            'model' => Faq::class, 'title' => 'پرسش‌های متداول', 'singular' => 'پرسش', 'primary' => 'question',
            'translatable' => ['question' => ['پرسش', 'text', true], 'answer' => ['پاسخ', 'rich']],
            'fields' => ['group' => ['گروه (general برای عمومی)', 'text']],
        ],
        'testimonials' => [
            'model' => Testimonial::class, 'title' => 'نظرات مشتریان', 'singular' => 'نظر', 'primary' => 'author_name',
            'translatable' => ['author_name' => ['نام', 'text', true], 'author_title' => ['سمت / شهر', 'text'], 'body' => ['متن نظر', 'textarea']],
            'fields' => ['avatar_media_id' => ['تصویر', 'media'], 'rating' => ['امتیاز (۱ تا ۵)', 'number']],
        ],
        'timeline' => [
            'model' => TimelineEntry::class, 'title' => 'تایم‌لاین و مسیر محصول', 'singular' => 'مرحله', 'primary' => 'title',
            'translatable' => ['title' => ['عنوان', 'text', true], 'body' => ['توضیح', 'textarea']],
            'fields' => ['group' => ['گروه', 'group'], 'label' => ['برچسب (مثلاً سال)', 'text'], 'media_id' => ['تصویر', 'media']],
        ],
        'certificates' => [
            'model' => Certificate::class, 'title' => 'گواهی‌ها و افتخارات', 'singular' => 'گواهی', 'primary' => 'title',
            'translatable' => ['title' => ['عنوان', 'text', true], 'description' => ['توضیح', 'textarea']],
            'fields' => ['issuer' => ['صادرکننده', 'text'], 'issued_at' => ['تاریخ صدور', 'date'], 'media_id' => ['تصویر', 'media']],
        ],
    ];

    public function index(Request $request, string $type)
    {
        $def = self::TYPES[$type];
        $items = $def['model']::with('translations')
            ->when($request->filled('group') && $type !== 'certificates' && $type !== 'testimonials', fn ($q) => $q->where('group', $request->query('group')))
            ->orderBy('sort_order')->get();

        return view('admin.blocks.index', compact('type', 'def', 'items'));
    }

    public function create(string $type)
    {
        $def = self::TYPES[$type];

        return view('admin.blocks.form', ['type' => $type, 'def' => $def, 'item' => new $def['model'](['is_active' => true])]);
    }

    public function store(Request $request, string $type)
    {
        $def = self::TYPES[$type];
        $this->save(new $def['model'], $request, $def);

        return redirect()->route('admin.blocks.index', $type)->with('success', 'ذخیره شد.');
    }

    public function edit(string $type, int $id)
    {
        $def = self::TYPES[$type];

        return view('admin.blocks.form', ['type' => $type, 'def' => $def, 'item' => $def['model']::with('translations')->findOrFail($id)]);
    }

    public function update(Request $request, string $type, int $id)
    {
        $def = self::TYPES[$type];
        $this->save($def['model']::findOrFail($id), $request, $def);

        return back()->with('success', 'تغییرات ذخیره شد.');
    }

    public function destroy(string $type, int $id)
    {
        self::TYPES[$type]['model']::findOrFail($id)->delete();

        return back()->with('success', 'حذف شد.');
    }

    private function save($item, Request $request, array $def): void
    {
        $rules = ['sort_order' => ['nullable', 'integer']];
        foreach ($def['fields'] as $f => [$label, $kind]) {
            $rules[$f] = match ($kind) {
                'media' => ['nullable', 'integer', 'exists:media,id'],
                'number' => ['nullable', 'integer', 'between:1,5'],
                'date' => ['nullable', 'date'],
                'group' => ['required', 'in:'.implode(',', array_keys(TimelineEntry::GROUPS))],
                default => ['nullable', 'string', 'max:191'],
            };
        }
        foreach (Locales::codes() as $l) {
            foreach ($def['translatable'] as $f => $fdef) {
                $rules["translations.$l.$f"] = [($fdef[2] ?? false) && $l === 'fa' ? 'required' : 'nullable', 'string', 'max:20000'];
            }
        }
        $data = $request->validate($rules);

        DB::transaction(function () use ($item, $data, $def, $request) {
            foreach (array_keys($def['fields']) as $f) {
                $item->{$f} = $data[$f] ?? null;
            }
            if (array_key_exists('group', $def['fields']) && ! $item->group) {
                $item->group = 'general';
            }
            $item->sort_order = (int) ($data['sort_order'] ?? 0);
            $item->is_active = $request->boolean('is_active');
            $item->save();
            $item->saveTranslations($data['translations'] ?? [], $def['primary']);
        });
    }
}
