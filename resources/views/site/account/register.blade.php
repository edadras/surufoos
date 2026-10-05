@extends('layouts.site')
@section('hide_footer_cta', true)
@section('content')
<section class="section">
    <div class="container auth-wrap">
        <form action="{{ lroute('account.register') }}" method="post" class="form-card">
            @csrf
            <h1 class="display-3">{{ __('ui.account.register') }}</h1>
            @if ($errors->any())<div class="form-errors" role="alert"><ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
            <div class="field"><label for="r-name">{{ __('ui.form.name') }}</label><input id="r-name" name="name" value="{{ old('name') }}" required autocomplete="name"></div>
            <div class="field"><label for="r-email">{{ __('ui.form.email') }}</label><input id="r-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" dir="ltr"></div>
            <div class="field"><label for="r-pass">{{ __('ui.form.password') }}</label><input id="r-pass" type="password" name="password" required autocomplete="new-password" dir="ltr"></div>
            <div class="field"><label for="r-pass2">{{ __('ui.form.password_confirmation') }}</label><input id="r-pass2" type="password" name="password_confirmation" required autocomplete="new-password" dir="ltr"></div>
            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hp" aria-hidden="true">
            <button class="btn btn--wide" type="submit">{{ __('ui.account.register') }}</button>
            <p class="muted">{{ __('ui.account.have_account') }} <a href="{{ lroute('account.login') }}">{{ __('ui.account.login') }}</a></p>
        </form>
    </div>
</section>
@endsection
