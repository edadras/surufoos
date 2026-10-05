@extends('admin.layout')
@section('title', 'پیام از '.$contact->name)
@section('content')
<div class="a-head"><h1>پیام از {{ $contact->name }}</h1><a href="{{ route('admin.contacts.index') }}" class="a-btn a-btn--ghost">بازگشت</a></div>
<div class="a-grid">
    <div class="a-grid__main">
        <section class="a-card">
            @if ($contact->subject)<h2>{{ $contact->subject }}</h2>@endif
            <div class="a-message">{!! nl2br(e($contact->message)) !!}</div>
        </section>
        <form method="post" action="{{ route('admin.contacts.update', $contact) }}" class="a-card">@csrf @method('PUT')
            <div class="a-field"><label for="reply_note">یادداشت پیگیری (داخلی)</label><textarea id="reply_note" name="reply_note" rows="4">{{ old('reply_note', $contact->reply_note) }}</textarea></div>
            <label class="a-check"><input type="checkbox" name="unread" value="1"> علامت به عنوان خوانده‌نشده</label>
            <button class="a-btn a-btn--sm">ذخیره</button>
        </form>
    </div>
    <aside class="a-grid__side"><section class="a-card">
        <dl class="a-dl">
            <dt>موبایل</dt><dd dir="ltr">@if($contact->mobile)<a href="tel:{{ $contact->mobile }}">{{ $contact->mobile }}</a>@else — @endif</dd>
            <dt>ایمیل</dt><dd dir="ltr">@if($contact->email)<a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a>@else — @endif</dd>
            <dt>زبان</dt><dd>{{ config("suru.locales.{$contact->locale}.native") }}</dd>
            <dt>تاریخ</dt><dd>{{ jdate($contact->created_at, 'd MMMM y HH:mm', 'fa') }}</dd>
            <dt>IP</dt><dd dir="ltr">{{ $contact->ip }}</dd>
        </dl>
        @include('admin.partials.delete-form', ['action' => route('admin.contacts.destroy', $contact), 'label' => 'حذف پیام'])
    </section></aside>
</div>
@endsection
