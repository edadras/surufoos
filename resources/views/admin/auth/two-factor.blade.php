<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex"><title>تأیید دومرحله‌ای</title>@vite(['resources/css/admin.css'])</head>
<body class="admin a-center">
    <form method="post" action="{{ route('admin.two-factor.challenge') }}" class="a-card a-auth">
        @csrf
        <h1>تأیید دومرحله‌ای</h1>
        <p class="a-muted">کد ۶ رقمی برنامه احراز هویت یا یکی از کدهای بازیابی را وارد کنید.</p>
        @if ($errors->any())<div class="a-alert a-alert--error">{{ $errors->first() }}</div>@endif
        <div class="a-field"><label for="code">کد</label><input id="code" name="code" required autofocus inputmode="numeric" autocomplete="one-time-code" dir="ltr"></div>
        <button class="a-btn a-btn--block">تأیید</button>
    </form>
    <form method="post" action="{{ route('admin.logout') }}" class="a-center-link">@csrf<button class="a-link">خروج</button></form>
</body>
</html>
