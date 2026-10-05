@extends('admin.layout')
@section('title', 'منو: '.$menu->name)
@section('content')
<div class="a-head"><h1>منو: {{ $menu->name }}</h1><a href="{{ route('admin.menus.index') }}" class="a-btn a-btn--ghost">بازگشت</a></div>
<div class="a-grid">
    <div class="a-grid__main">
        <section class="a-card">
            <h2>آیتم‌ها <small>برای تغییر ترتیب بکشید</small></h2>
            <ol class="a-sections" data-sortable data-reorder-url="{{ route('admin.menus.reorder', $menu) }}">
                @forelse ($menu->rootItems as $item)
                    <li class="a-section" data-id="{{ $item->id }}">
                        <span class="a-drag">⋮⋮</span>
                        <details class="a-section__info">
                            <summary><strong>{{ $item->label('fa') }}</strong> <small>{{ ['route' => 'صفحه سیستمی', 'url' => 'لینک دلخواه', 'category' => 'دسته', 'product' => 'محصول', 'page' => 'صفحه'][$item->type] }}{{ $item->is_mega ? ' · مگامنو' : '' }}</small></summary>
                            <form method="post" action="{{ route('admin.menus.items.update', [$menu, $item]) }}" class="a-menu-form">@csrf @method('PUT')
                                @include('admin.menus._item-fields', ['item' => $item])
                                <button class="a-btn a-btn--sm">ذخیره آیتم</button>
                            </form>
                            @if ($item->children->isNotEmpty())
                                <ul class="a-sub">@foreach ($item->children as $child)<li>{{ $child->label('fa') }} <form method="post" action="{{ route('admin.menus.items.destroy', [$menu, $child]) }}" class="a-inline" data-confirm="حذف شود؟">@csrf @method('DELETE')<button class="a-link-danger">حذف</button></form></li>@endforeach</ul>
                            @endif
                        </details>
                        @include('admin.partials.tr-status', ['model' => $item])
                        @include('admin.partials.delete-form', ['action' => route('admin.menus.items.destroy', [$menu, $item])])
                    </li>
                @empty
                    <li class="a-empty">آیتمی ندارد.</li>
                @endforelse
            </ol>
        </section>
    </div>
    <aside class="a-grid__side">
        <section class="a-card">
            <h2>افزودن آیتم</h2>
            <form method="post" action="{{ route('admin.menus.items.store', $menu) }}" class="a-menu-form">@csrf
                @include('admin.menus._item-fields', ['item' => new \App\Models\MenuItem(['type' => 'route', 'is_active' => true])])
                <button class="a-btn a-btn--sm">افزودن</button>
            </form>
        </section>
    </aside>
</div>
@endsection
