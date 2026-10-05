<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\IngredientRequest;
use App\Models\Ingredient;

class IngredientController extends Controller
{
    public function index()
    {
        return view('admin.ingredients.index', ['ingredients' => Ingredient::with(['translations', 'media'])->withCount('products')->latest()->paginate(40)]);
    }

    public function create()
    {
        return view('admin.ingredients.form', ['ingredient' => new Ingredient]);
    }

    public function store(IngredientRequest $request)
    {
        $ingredient = Ingredient::create($request->safe()->except('translations'));
        $ingredient->saveTranslations($request->translations(), 'name');

        return redirect()->route('admin.ingredients.index')->with('success', 'ماده اولیه ذخیره شد.');
    }

    public function edit(Ingredient $ingredient)
    {
        return view('admin.ingredients.form', ['ingredient' => $ingredient->load('translations')]);
    }

    public function update(IngredientRequest $request, Ingredient $ingredient)
    {
        $ingredient->update($request->safe()->except('translations'));
        $ingredient->saveTranslations($request->translations(), 'name');

        return back()->with('success', 'تغییرات ذخیره شد.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $ingredient->delete();

        return redirect()->route('admin.ingredients.index')->with('success', 'حذف شد.');
    }
}
