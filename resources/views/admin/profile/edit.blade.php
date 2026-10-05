@extends('admin.layout')
@section('title', 'پروفایل')
@section('content')
<div class="a-grid">
    <div class="a-grid__main">
        <form method="post" action="{{ route('admin.profile.update') }}" class="a-card">
            @csrf @method('PUT')
            <h2>اطلاعات حساب</h2>
            <div class="a-row">
                <div class="a-field"><label for="name">نام</label><input id="name" name="name" value="{{ old('name', $user->name) }}" required></div>
                <div class="a-field"><label for="email">ایمیل</label><input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required dir="ltr"></div>
            </div>
            <h3>تغییر رمز عبور</h3>
            <div class="a-row">
                <div class="a-field"><label for="current_password">رمز فعلی</label><input id="current_password" type="password" name="current_password" dir="ltr" autocomplete="current-password"></div>
                <div class="a-field"><label for="password">رمز جدید (حداقل ۱۰ نویسه، حرف و عدد)</label><input id="password" type="password" name="password" dir="ltr" autocomplete="new-password"></div>
                <div class="a-field"><label for="password_confirmation">تکرار رمز جدید</label><input id="password_confirmation" type="password" name="password_confirmation" dir="ltr" autocomplete="new-password"></div>
            </div>
            <button class="a-btn">ذخیره</button>
        </form>
    </div>
    <aside class="a-grid__side">
        <section class="a-card">
            <h2>ورود دومرحله‌ای (2FA)</h2>
            @if ($recoveryCodes)
                <div class="a-alert a-alert--warn"><strong>کدهای بازیابی را در جای امن نگه دارید (فقط یک بار نمایش داده می‌شوند):</strong>
                    <ul class="a-codes" dir="ltr">@foreach ($recoveryCodes as $c)<li>{{ $c }}</li>@endforeach</ul></div>
            @endif
            @if ($user->hasTwoFactorEnabled())
                <p>ورود دومرحله‌ای <strong>فعال</strong> است.</p>
                <form method="post" action="{{ route('admin.profile.2fa.disable') }}">@csrf @method('DELETE')
                    <div class="a-field"><label for="cp2">رمز فعلی برای غیرفعال‌سازی</label><input id="cp2" type="password" name="current_password" required dir="ltr"></div>
                    <button class="a-btn a-btn--danger a-btn--sm">غیرفعال کردن</button>
                </form>
            @elseif ($pendingSecret)
                <p>این کد QR را با Google Authenticator یا برنامه مشابه اسکن کنید و کد ۶ رقمی را وارد کنید.</p>
                <div class="a-qr">{!! $qr !!}</div>
                <p class="a-hint" dir="ltr">{{ chunk_split($pendingSecret, 4, ' ') }}</p>
                <form method="post" action="{{ route('admin.profile.2fa.confirm') }}">@csrf
                    <div class="a-field"><label for="code">کد تأیید</label><input id="code" name="code" required inputmode="numeric" dir="ltr" autocomplete="one-time-code"></div>
                    <button class="a-btn a-btn--sm">فعال‌سازی</button>
                </form>
            @else
                <p class="a-muted">برای امنیت بیشتر، ورود دومرحله‌ای را فعال کنید.</p>
                <form method="post" action="{{ route('admin.profile.2fa.enable') }}">@csrf<button class="a-btn a-btn--sm">شروع فعال‌سازی</button></form>
            @endif
        </section>
    </aside>
</div>
@endsection
