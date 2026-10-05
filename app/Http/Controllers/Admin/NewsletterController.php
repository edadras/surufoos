<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        $subscribers = NewsletterSubscriber::when($request->filled('q'), fn ($q) => $q->where('email', 'like', '%'.$request->query('q').'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->latest()->paginate(50)->withQueryString();

        return view('admin.newsletter.index', compact('subscribers'));
    }

    public function export()
    {
        $filename = 'newsletter-'.now()->format('Ymd').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['email', 'locale', 'status', 'created_at']);
            NewsletterSubscriber::where('status', 'active')->orderBy('id')->chunk(500, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, [$r->email, $r->locale, $r->status, $r->created_at]);
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function destroy(NewsletterSubscriber $subscriber)
    {
        $subscriber->delete();

        return back()->with('success', 'حذف شد.');
    }
}
