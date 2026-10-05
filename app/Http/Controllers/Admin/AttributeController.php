<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AttributeRequest;
use App\Models\Attribute;
use Illuminate\Support\Facades\DB;

class AttributeController extends Controller
{
    public function index()
    {
        return view('admin.attributes.index', ['attributes' => Attribute::with(['translations', 'values.translations'])->orderBy('sort_order')->get()]);
    }

    public function create()
    {
        return view('admin.attributes.form', ['attribute' => new Attribute(['type' => 'multi', 'is_filterable' => true])]);
    }

    public function store(AttributeRequest $request)
    {
        $attribute = $this->save(new Attribute, $request);

        return redirect()->route('admin.attributes.edit', $attribute)->with('success', 'ویژگی ذخیره شد.');
    }

    public function edit(Attribute $attribute)
    {
        return view('admin.attributes.form', ['attribute' => $attribute->load(['translations', 'values.translations'])]);
    }

    public function update(AttributeRequest $request, Attribute $attribute)
    {
        $this->save($attribute, $request);

        return back()->with('success', 'تغییرات ذخیره شد.');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();

        return redirect()->route('admin.attributes.index')->with('success', 'حذف شد.');
    }

    private function save(Attribute $attribute, AttributeRequest $request): Attribute
    {
        return DB::transaction(function () use ($attribute, $request) {
            $data = $request->validated();
            $attribute->fill(collect($data)->only(['code', 'type', 'is_filterable', 'is_quiz'])->all());
            $attribute->sort_order = (int) ($data['sort_order'] ?? 0);
            $attribute->save();
            $attribute->saveTranslations($request->translations(), 'name');

            $keep = [];
            foreach (array_values($data['values'] ?? []) as $i => $row) {
                $value = $attribute->values()->firstOrNew(['id' => $row['id'] ?? null]);
                $value->fill(['code' => $row['code'], 'color' => $row['color'] ?? null, 'sort_order' => $i]);
                $value->attribute_id = $attribute->id;
                $value->save();
                $value->saveTranslations(collect(['fa', 'en', 'ar'])->mapWithKeys(fn ($l) => [$l => ['value' => $row[$l] ?? null]])->all(), 'value');
                $keep[] = $value->id;
            }
            $attribute->values()->whereNotIn('id', $keep)->delete();

            return $attribute;
        });
    }
}
