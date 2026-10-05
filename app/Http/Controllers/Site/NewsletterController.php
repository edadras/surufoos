<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:191'], 'website' => ['prohibited']]);

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => mb_strtolower($data['email'])]);
        $subscriber->fill(['locale' => app()->getLocale(), 'status' => 'active']);
        $subscriber->unsubscribe_token ??= Str::random(48);
        $subscriber->save();

        return $request->wantsJson()
            ? response()->json(['message' => __('ui.newsletter.thanks')])
            : back()->with('status', __('ui.newsletter.thanks'));
    }

    public function unsubscribe(string $token)
    {
        NewsletterSubscriber::where('unsubscribe_token', $token)->update(['status' => 'unsubscribed']);

        return redirect()->route('home')->with('status', __('ui.newsletter.unsubscribed'));
    }
}
