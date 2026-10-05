<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $contacts = Contact::query()
            ->when($request->boolean('unread'), fn ($q) => $q->unread())
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%'.$request->query('q').'%')
                ->orWhere('email', 'like', '%'.$request->query('q').'%')->orWhere('mobile', 'like', '%'.$request->query('q').'%')->orWhere('message', 'like', '%'.$request->query('q').'%')))
            ->latest()->paginate(30)->withQueryString();

        return view('admin.contacts.index', compact('contacts'));
    }

    public function show(Contact $contact)
    {
        if (! $contact->read_at) {
            $contact->update(['read_at' => now()]);
        }

        return view('admin.contacts.show', compact('contact'));
    }

    public function update(Request $request, Contact $contact)
    {
        $data = $request->validate(['reply_note' => ['nullable', 'string', 'max:5000'], 'unread' => ['nullable', 'boolean']]);
        $contact->update(['reply_note' => $data['reply_note'] ?? null, 'read_at' => $request->boolean('unread') ? null : ($contact->read_at ?? now())]);

        return back()->with('success', 'ذخیره شد.');
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect()->route('admin.contacts.index')->with('success', 'پیام حذف شد.');
    }
}
