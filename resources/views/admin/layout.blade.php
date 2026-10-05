@php
    $user = auth()->user();
    $nav = [
        ['داشبورد', 'admin.dashboard', 'dashboard.view', 'grid'],
        ['—', 'کاتالوگ'],
        ['محصولات', 'admin.products.index', 'products.manage', 'bag'],
        ['دسته‌بندی محصولات', 'admin.categories.index', 'products.manage', 'grid'],
        ['ویژگی‌ها', 'admin.attributes.index', 'products.manage', 'filter'],
        ['مواد اولیه', 'admin.ingredients.index', 'products.manage', 'leaf'],
        ['—', 'محتوا'],
        ['صفحات و صفحه‌ساز', 'admin.pages.index', 'content.manage', 'home'],
        ['اسلایدر (هیرو)', 'admin.sliders.index', 'content.manage', 'eye'],
        ['بنرها', 'admin.banners.index', 'content.manage', 'eye'],
        ['منوها', 'admin.menus.index', 'content.manage', 'menu'],
        ['مجله', 'admin.posts.index', 'content.manage', 'mail'],
        ['دسته‌های مجله', 'admin.post-categories.index', 'content.manage', 'menu'],
        ['دستورهای غذایی', 'admin.recipes.index', 'content.manage', 'flame'],
        ['پرسش‌های متداول', ['admin.blocks.index', ['type' => 'faqs']], 'content.manage', 'plus'],
        ['نظرات مشتریان', ['admin.blocks.index', ['type' => 'testimonials']], 'content.manage', 'users'],
        ['تایم‌لاین / مسیر محصول', ['admin.blocks.index', ['type' => 'timeline']], 'content.manage', 'clock'],
        ['گواهی‌ها', ['admin.blocks.index', ['type' => 'certificates']], 'content.manage', 'check'],
        ['دیدگاه‌ها', 'admin.comments.index', 'content.manage', 'mail'],
        ['رسانه‌ها', 'admin.media.index', 'media.manage', 'eye'],
        ['—', 'ارتباطات'],
        ['پیام‌ها', 'admin.contacts.index', 'messages.manage', 'mail'],
        ['خبرنامه', 'admin.newsletter.index', 'messages.manage', 'mail'],
        ['سفارش‌ها', 'admin.orders.index', 'orders.manage', 'bag'],
        ['—', 'سیستم'],
        ['مدیریت ترجمه', 'admin.translations.index', 'translations.manage', 'globe'],
        ['زبان‌ها', 'admin.languages.index', 'settings.manage', 'globe'],
        ['سئو', 'admin.seo.index', 'seo.manage', 'search'],
        ['ریدایرکت‌ها', 'admin.redirects.index', 'seo.manage', 'arrow'],
        ['کاربران', 'admin.users.index', 'users.manage', 'user'],
        ['نقش‌ها و دسترسی‌ها', 'admin.roles.index', 'users.manage', 'users'],
        ['تنظیمات', 'admin.settings.edit', 'settings.manage', 'filter'],
        ['گزارش‌ها', 'admin.reports.index', 'reports.view', 'grid'],
        ['گزارش فعالیت', 'admin.activity.index', 'reports.view', 'clock'],
        ['درون‌ریزی Suru Food', 'admin.import.index', 'import.manage', 'arrow'],
    ];
    $unread = $user->hasPermission('messages.manage') ? \App\Models\Contact::unread()->count() : 0;
@endphp
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'مدیریت') — پنل مدیریت {{ settings()->siteName('fa') }}</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="admin">
    @include('partials.icons')
    <aside class="a-side" data-side>
        <a href="{{ route('admin.dashboard') }}" class="a-brand">{{ settings()->siteName('fa') }} <small>پنل مدیریت</small></a>
        <nav class="a-nav" aria-label="منوی مدیریت">
            @php $pendingHeading = null; @endphp
            @foreach ($nav as $item)
                @if ($item[0] === '—')
                    @php $pendingHeading = $item[1]; @endphp
                    @continue
                @endif
                @php
                    [$routeName, $params] = is_array($item[1]) ? $item[1] : [$item[1], []];
                    if (! $user->hasPermission($item[2]) && $item[2] !== 'dashboard.view') continue;
                    $base = preg_replace('/\.(index|edit)$/', '', $routeName);
                    $active = request()->routeIs($base.'*') && (! $params || request()->route('type') === ($params['type'] ?? null));
                @endphp
                @if ($pendingHeading)
                    <p class="a-nav__heading">{{ $pendingHeading }}</p>
                    @php $pendingHeading = null; @endphp
                @endif
                <a href="{{ route($routeName, $params) }}" class="{{ $active ? 'is-active' : '' }}">
                    <x-icon :name="$item[3]" size="18"/> <span>{{ $item[0] }}</span>
                    @if ($routeName === 'admin.contacts.index' && $unread)<b class="a-badge">{{ digits($unread, 'fa') }}</b>@endif
                </a>
            @endforeach
        </nav>
    </aside>
    <div class="a-main">
        <header class="a-top">
            <button type="button" class="a-icon-btn a-only-mobile" data-side-toggle aria-label="منو"><x-icon name="menu"/></button>
            <div class="a-top__title">@yield('title', 'مدیریت')</div>
            <div class="a-top__tools">
                <a href="{{ route('home', ['locale' => 'fa']) }}" target="_blank" class="a-btn a-btn--ghost a-btn--sm">مشاهده سایت</a>
                <a href="{{ route('admin.profile') }}" class="a-btn a-btn--ghost a-btn--sm"><x-icon name="user" size="16"/> {{ $user->name }}</a>
                <form action="{{ route('admin.logout') }}" method="post">@csrf<button class="a-btn a-btn--ghost a-btn--sm" type="submit">خروج</button></form>
            </div>
        </header>
        <main class="a-content">
            @if (session('success'))<div class="a-alert a-alert--success" role="status">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="a-alert a-alert--error" role="alert">{{ session('error') }}</div>@endif
            @if ($errors->any())
                <div class="a-alert a-alert--error" role="alert"><strong>لطفاً خطاهای فرم را برطرف کنید:</strong><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif
            @yield('content')
        </main>
    </div>
    <dialog class="a-modal" data-media-modal>
        <div class="a-modal__head"><strong>انتخاب از رسانه‌ها</strong><button type="button" class="a-icon-btn" data-modal-close aria-label="بستن"><x-icon name="close"/></button></div>
        <div class="a-modal__body" data-media-modal-body></div>
    </dialog>
    <script type="application/json" id="admin-config">{!! json_encode(['pickerUrl' => route('admin.media.picker'), 'uploadUrl' => route('admin.media.store')], JSON_HEX_TAG) !!}</script>
</body>
</html>
