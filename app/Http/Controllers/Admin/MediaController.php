<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateMediaVariants;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Services\MediaService;
use App\Support\Locales;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MediaController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(Request $request)
    {
        return view('admin.media.index', [
            'items' => $this->query($request)->paginate(48)->withQueryString(),
            'folders' => MediaFolder::orderBy('name')->get(),
        ]);
    }

    /** HTML fragment for the media picker modal. */
    public function picker(Request $request)
    {
        return view('admin.media._picker', [
            'items' => $this->query($request)->where('mime_type', 'like', 'image/%')->paginate(36)->withQueryString(),
            'folders' => MediaFolder::orderBy('name')->get(),
            'multiple' => $request->boolean('multiple'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['file', 'max:'.config('suru.images.max_upload_kb'), 'mimetypes:'.implode(',', array_merge(config('suru.images.allowed_mimes'), ['video/mp4', 'video/webm']))],
            'folder_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ]);
        $folder = $request->integer('folder_id') ? MediaFolder::find($request->integer('folder_id')) : null;

        $created = [];
        $errors = [];
        foreach ($request->file('files') as $file) {
            try {
                $created[] = $this->media->storeUpload($file, $folder, $request->user()->id);
            } catch (InvalidArgumentException $e) {
                $errors[] = $file->getClientOriginalName().': '.$e->getMessage();
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'items' => collect($created)->map(fn ($m) => ['id' => $m->id, 'thumb' => $m->variantUrl('thumbnail'), 'url' => $m->url()]),
                'errors' => $errors,
            ], $created ? 200 : 422);
        }

        return back()->with($errors ? 'error' : 'success', $errors ? implode(' | ', $errors) : count($created).' فایل بارگذاری شد.');
    }

    public function edit(Media $media)
    {
        return view('admin.media.edit', ['media' => $media->load('translations'), 'folders' => MediaFolder::orderBy('name')->get()]);
    }

    public function update(Request $request, Media $media)
    {
        $rules = ['focal_x' => ['required', 'numeric', 'between:0,100'], 'focal_y' => ['required', 'numeric', 'between:0,100'], 'folder_id' => ['nullable', 'integer', 'exists:media_folders,id']];
        foreach (Locales::codes() as $l) {
            $rules["translations.$l.alt"] = ['nullable', 'string', 'max:250'];
            $rules["translations.$l.caption"] = ['nullable', 'string', 'max:500'];
        }
        $data = $request->validate($rules);
        $media->update(collect($data)->only(['focal_x', 'focal_y', 'folder_id'])->all());
        foreach (Locales::codes() as $l) {
            $t = array_filter($data['translations'][$l] ?? [], 'filled');
            $t ? $media->translations()->updateOrCreate(['locale' => $l], $t + ['alt' => null, 'caption' => null]) : $media->translations()->where('locale', $l)->delete();
        }

        return back()->with('success', 'اطلاعات تصویر ذخیره شد.');
    }

    public function destroy(Media $media)
    {
        $this->media->delete($media);

        return request()->wantsJson() ? response()->json(['ok' => true]) : redirect()->route('admin.media.index')->with('success', 'فایل حذف شد.');
    }

    public function regenerate(Media $media)
    {
        $media->update(['conversion_status' => 'pending']);
        GenerateMediaVariants::dispatch($media->id);

        return back()->with('success', 'ساخت نسخه‌های تصویر در صف قرار گرفت.');
    }

    public function storeFolder(Request $request)
    {
        MediaFolder::create($request->validate(['name' => ['required', 'string', 'max:100'], 'parent_id' => ['nullable', 'integer', 'exists:media_folders,id']]));

        return back()->with('success', 'پوشه ساخته شد.');
    }

    private function query(Request $request)
    {
        return Media::with('translations')
            ->when($request->integer('folder'), fn ($q, $f) => $q->where('folder_id', $f))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('original_name', 'like', '%'.$request->query('q').'%')
                ->orWhereHas('translations', fn ($t) => $t->where('alt', 'like', '%'.$request->query('q').'%'))))
            ->when($request->boolean('missing_alt'), fn ($q) => $q->missingAlt())
            ->when($request->boolean('failed'), fn ($q) => $q->where('conversion_status', 'failed'))
            ->latest('id');
    }
}
