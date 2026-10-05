<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex"><title>ورود به پنل مدیریت</title>@vite(['resources/css/admin.css'])</head>
<body class="admin a-center">
    <form method="post" action="{{ route('admin.login') }}" class="a-card a-auth">
        @csrf
        <h1>ورود به پنل مدیریت</h1>
        <p class="a-muted">{{ settings()->siteName('fa') }}</p>
        @if ($errors->any())<div class="a-alert a-alert--error">{{ $errors->first() }}</div>@endif
        <div class="a-field"><label for="email">ایمیل</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus dir="ltr" autocomplete="username"></div>
        <div class="a-field"><label for="password">رمز عبور</label><input id="password" type="password" name="password" required dir="ltr" autocomplete="current-password"></div>
        <label class="a-check"><input type="checkbox" name="remember" value="1"> مرا به خاطر بسپار</label>
        <button class="a-btn a-btn--block">ورود</button>
    </form>
</body>
</html>
