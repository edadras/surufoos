@extends('layouts.site')
@section('hide_footer_cta', true)
@section('content')
<section class="section">
    <div class="container auth-wrap">
        <form action="{{ lroute('account.login') }}" method="post" class="form-card">
            @csrf
            <h1 class="display-3">{{ __('ui.account.login') }}</h1>
            @if ($errors->any())<div class="form-errors" role="alert">{{ $errors->first() }}</div>@endif
            <div class="field"><label for="l-email">{{ __('ui.form.email') }}</label><input id="l-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" dir="ltr"></div>
            <div class="field"><label for="l-pass">{{ __('ui.form.password') }}</label><input id="l-pass" type="password" name="password" required autocomplete="current-password" dir="ltr"></div>
            <label class="radio"><input type="checkbox" name="remember" value="1"> <span>{{ __('ui.form.remember') }}</span></label>
            <button class="btn btn--wide" type="submit">{{ __('ui.account.login') }}</button>
            <p class="muted">{{ __('ui.account.no_account') }} <a href="{{ lroute('account.register') }}">{{ __('ui.account.register') }}</a></p>
        </form>
    </div>
</section>
@endsection
