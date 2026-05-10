<x-mail::message>
# New contact message

You've received a new message via the website contact form.

**From:** {{ $message->name }} ({{ $message->email }})
**Subject:** {{ $message->subject }}

---

{{ $message->message }}

---

Reply directly to this email to respond.

{{ config('app.name') }}
</x-mail::message>
