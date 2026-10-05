@php($l = in_array(request()->segment(1), ['fa', 'en', 'ar'], true) ? request()->segment(1) : 'fa')
<!DOCTYPE html>
<html lang="{{ $l }}" dir="{{ $l === 'en' ? 'ltr' : 'rtl' }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>500</title>
<style>body{margin:0;min-height:100vh;display:grid;place-items:center;background:#F5EFE4;color:#1F2A22;font:16px/1.7 system-ui,Tahoma,sans-serif;text-align:center;padding:24px}h1{font-size:clamp(28px,5vw,44px);margin:.2em 0}a{color:inherit}b{font-size:96px;opacity:.15;display:block;line-height:1}</style></head>
<body><main><b>500</b><h1>{{ __('ui.errors.500_title', [], $l) }}</h1><p>{{ __('ui.errors.500_text', [], $l) }}</p><p><a href="/{{ $l }}">{{ __('ui.errors.home', [], $l) }}</a></p></main></body></html>
