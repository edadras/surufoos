<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Redirect;
use App\Support\CacheGroups;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RedirectController extends Controller
{
    public function index(Request $request)
    {
        $redirects = Redirect::when($request->filled('q'), fn ($q) => $q->where('from_path', 'like', '%'.$request->query('q').'%')->orWhere('to_path', 'like', '%'.$request->query('q').'%'))
            ->latest()->paginate(50)->withQueryString();

        return view('admin.redirects.index', compact('redirects'));
    }

    public function create()
    {
        return view('admin.redirects.form', ['redirect' => new Redirect(['status_code' => 301, 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        Redirect::create($this->validated($request));
        CacheGroups::flush(CacheGroups::CONTENT);

        return redirect()->route('admin.redirects.index')->with('success', 'ریدایرکت ذخیره شد.');
    }

    public function edit(Redirect $redirect)
    {
        return view('admin.redirects.form', compact('redirect'));
    }

    public function update(Request $request, Redirect $redirect)
    {
        $redirect->update($this->validated($request, $redirect));
        CacheGroups::flush(CacheGroups::CONTENT);

        return back()->with('success', 'ذخیره شد.');
    }

    public function destroy(Redirect $redirect)
    {
        $redirect->delete();
        CacheGroups::flush(CacheGroups::CONTENT);

        return back()->with('success', 'حذف شد.');
    }

    private function validated(Request $request, ?Redirect $redirect = null): array
    {
        $request->merge(['from_path' => Redirect::normalizePath((string) $request->input('from_path'))]);
        $data = $request->validate([
            'from_path' => ['required', 'string', 'max:500', Rule::unique('redirects', 'from_path')->ignore($redirect?->id), 'not_in:/'],
            'to_path' => ['required', 'string', 'max:500', 'different:from_path'],
            'status_code' => ['required', Rule::in([301, 302, 307, 308])],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
