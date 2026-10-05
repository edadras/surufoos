<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Product;
use App\Support\CacheGroups;
use App\Support\Locales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    private const LINKABLE = ['category' => Category::class, 'product' => Product::class, 'page' => Page::class];

    public function index()
    {
        return view('admin.menus.index', ['menus' => Menu::withCount('items')->get()]);
    }

    public function edit(Menu $menu)
    {
        return view('admin.menus.edit', [
            'menu' => $menu->load(['rootItems.translations', 'rootItems.children.translations']),
            'categories' => Category::with('translations')->get(),
            'pages' => Page::with('translations')->whereNull('key')->get(),
            'products' => Product::with('translations')->latest('id')->limit(300)->get(),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $menu->update($request->validate(['name' => ['required', 'string', 'max:100']]));

        return back()->with('success', 'ذخیره شد.');
    }

    public function storeItem(Request $request, Menu $menu)
    {
        $item = new MenuItem(['menu_id' => $menu->id, 'sort_order' => (int) $menu->items()->max('sort_order') + 1]);
        $this->saveItem($item, $request, $menu);

        return back()->with('success', 'آیتم اضافه شد.');
    }

    public function updateItem(Request $request, Menu $menu, MenuItem $item)
    {
        abort_unless($item->menu_id === $menu->id, 404);
        $this->saveItem($item, $request, $menu);

        return back()->with('success', 'آیتم ذخیره شد.');
    }

    public function destroyItem(Menu $menu, MenuItem $item)
    {
        abort_unless($item->menu_id === $menu->id, 404);
        $item->delete();

        return back()->with('success', 'آیتم حذف شد.');
    }

    public function reorder(Request $request, Menu $menu)
    {
        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];
        DB::transaction(fn () => collect($ids)->each(fn ($id, $i) => $menu->items()->whereKey($id)->update(['sort_order' => $i])));
        CacheGroups::flush(CacheGroups::MENUS);

        return response()->json(['ok' => true]);
    }

    private function saveItem(MenuItem $item, Request $request, Menu $menu): void
    {
        $rules = [
            'type' => ['required', Rule::in(['route', 'url', 'category', 'product', 'page'])],
            'route_name' => ['nullable', 'required_if:type,route', Rule::in(array_keys(MenuItem::ROUTES))],
            'linkable_id' => ['nullable', 'required_if:type,category,product,page', 'integer'],
            'parent_id' => ['nullable', 'integer', Rule::exists('menu_items', 'id')->where('menu_id', $menu->id)],
            'is_mega' => ['boolean'],
        ];
        foreach (Locales::codes() as $l) {
            $rules["translations.$l.label"] = [$l === 'fa' ? 'required' : 'nullable', 'string', 'max:100'];
            $rules["translations.$l.url"] = ['nullable', 'string', 'max:500'];
        }
        $request->merge(['is_mega' => $request->boolean('is_mega')]);
        $data = $request->validate($rules);

        DB::transaction(function () use ($item, $data, $request) {
            $item->fill([
                'type' => $data['type'],
                'route_name' => $data['type'] === 'route' ? $data['route_name'] : null,
                'parent_id' => $data['parent_id'] ?? null,
                'is_mega' => $data['is_mega'],
                'is_active' => $request->boolean('is_active', true),
            ]);
            if (isset(self::LINKABLE[$data['type']])) {
                $class = self::LINKABLE[$data['type']];
                $class::findOrFail($data['linkable_id']);
                $item->linkable_type = (new $class)->getMorphClass();
                $item->linkable_id = $data['linkable_id'];
            } else {
                $item->linkable_type = $item->linkable_id = null;
            }
            $item->save();
            $item->saveTranslations($data['translations'], 'label');
        });
        CacheGroups::flush(CacheGroups::MENUS);
    }
}
