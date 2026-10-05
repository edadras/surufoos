<?php

namespace App\Jobs;

use App\Models\Contact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class NotifyNewContactMessage implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $contactId) {}

    public function handle(): void
    {
        $to = settings('contact_notify_email') ?: settings('email');
        $contact = Contact::find($this->contactId);
        if (! $to || ! $contact) {
            return;
        }

        Mail::raw(
            "پیام جدید از فرم تماس\n\nنام: {$contact->name}\nموبایل: {$contact->mobile}\nایمیل: {$contact->email}\nموضوع: {$contact->subject}\n\n{$contact->message}\n\n".route('admin.contacts.show', $contact),
            function ($mail) use ($to, $contact) {
                $mail->to($to)->subject('پیام جدید: '.($contact->subject ?: $contact->name));
                if ($contact->email) {
                    $mail->replyTo($contact->email, $contact->name);
                }
            }
        );
    }
}
