<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Http\Requests\Site\ContactRequest;
use App\Jobs\NotifyNewContactMessage;
use App\Models\Contact;
use App\Models\Page;
use App\Services\Seo;

class ContactController extends Controller
{
    public function show(Seo $seo)
    {
        $page = Page::published()->where('key', 'contact')->with('translations')->first();
        $seo->fromTranslation($page?->translation())->forRoute('contact')->routeAlternates('contact');
        $seo->set($seo->title ?: __('ui.nav.contact'), $seo->description ?: __('ui.contact.intro'));
        $seo->breadcrumb(__('ui.nav.contact'));
        $seo->schema(['@type' => 'ContactPage', 'name' => __('ui.nav.contact'), 'url' => lroute('contact')]);

        return view('site.contact', ['page' => $page]);
    }

    public function store(ContactRequest $request)
    {
        $contact = Contact::create($request->validated() + [
            'locale' => app()->getLocale(),
            'ip' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
        ]);
        NotifyNewContactMessage::dispatch($contact->id);

        if ($request->wantsJson()) {
            return response()->json(['message' => __('ui.contact.sent')]);
        }

        return back()->with('status', __('ui.contact.sent'));
    }
}
