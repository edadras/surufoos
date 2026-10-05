<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Enums\SectionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PageRequest;
use App\Models\Page;

class PageController extends Controller
{
    public function index()
    {
        return view('admin.pages.index', ['pages' => Page::with('translations')->withCount('sections')->orderByRaw('`key` IS NULL')->orderBy('id')->get()]);
    }

    public function create()
    {
        return view('admin.pages.form', ['page' => new Page(['status' => ContentStatus::Draft])]);
    }

    public function store(PageRequest $request)
    {
        $page = Page::create($request->safe()->except('translations'));
        $page->saveTranslations($request->translations());

        return redirect()->route('admin.pages.edit', $page)->with('success', 'صفحه ساخته شد. حالا سکشن‌ها را اضافه کنید.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.form', [
            'page' => $page->load(['translations', 'sections.translations']),
            'types' => SectionType::options(),
        ]);
    }

    public function update(PageRequest $request, Page $page)
    {
        $page->update($request->safe()->except('translations'));
        $page->saveTranslations($request->translations());

        return back()->with('success', 'صفحه ذخیره شد.');
    }

    public function destroy(Page $page)
    {
        abort_if($page->isSystem(), 403, 'صفحات سیستمی قابل حذف نیستند.');
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'صفحه حذف شد.');
    }
}
