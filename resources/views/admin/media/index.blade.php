@extends('admin.layout')
@section('title', 'رسانه‌ها')
@section('content')
<div class="a-head"><h1>کتابخانه رسانه</h1></div>
<div class="a-card">
    <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="a-dropzone" data-dropzone>
        @csrf
        <input type="file" name="files[]" id="files" multiple accept="image/*,video/mp4,video/webm" required>
        <label for="files"><strong>فایل‌ها را اینجا رها کنید</strong> یا برای انتخاب کلیک کنید<br><small>JPG، PNG، WebP، GIF، SVG، AVIF، MP4 — حداکثر {{ digits(config('suru.images.max_upload_kb') / 1024, 'fa') }} مگابایت. نسخه اصلی نگه‌داری و نسخه‌های WebP/AVIF و اندازه‌های مختلف خودکار ساخته می‌شود.</small></label>
        <select name="folder_id"><option value="">بدون پوشه</option>@foreach ($folders as $f)<option value="{{ $f->id }}">{{ $f->name }}</option>@endforeach</select>
        <button class="a-btn a-btn--sm">بارگذاری</button>
    </form>
</div>
<div class="a-filters">
    <form method="get" class="a-filters">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="نام فایل یا متن جایگزین…">
        <select name="folder"><option value="">همه پوشه‌ها</option>@foreach ($folders as $f)<option value="{{ $f->id }}" @selected(request('folder') == $f->id)>{{ $f->name }}</option>@endforeach</select>
        <label class="a-check"><input type="checkbox" name="missing_alt" value="1" @checked(request('missing_alt'))> بدون ALT</label>
        <button class="a-btn a-btn--sm">فیلتر</button>
    </form>
    <form method="post" action="{{ route('admin.media.folders.store') }}" class="a-filters">@csrf<input name="name" placeholder="نام پوشه جدید" required><button class="a-btn a-btn--ghost a-btn--sm">+ پوشه</button></form>
</div>
<div class="a-media-grid">
    @forelse ($items as $m)
        <a href="{{ route('admin.media.edit', $m) }}" class="a-media-item {{ $m->alt('fa') ? '' : 'is-missing-alt' }}">
            @if ($m->isImage())<img src="{{ $m->variantUrl('thumbnail') }}" alt="{{ $m->alt('fa') }}" loading="lazy">@else<span class="a-media-item__file">{{ strtoupper(pathinfo($m->filename, PATHINFO_EXTENSION)) }}</span>@endif
            <small>{{ \Illuminate\Support\Str::limit($m->original_name, 26) }}</small>
            @if ($m->conversion_status !== 'done')<em class="a-pill a-pill--{{ $m->conversion_status }}">{{ $m->conversion_status === 'failed' ? 'خطا' : 'در صف' }}</em>@endif
        </a>
    @empty
        <p class="a-empty">فایلی وجود ندارد.</p>
    @endforelse
</div>
@include('admin.partials.pagination', ['paginator' => $items])
@endsection
