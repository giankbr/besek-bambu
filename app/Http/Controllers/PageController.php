<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function gallery()
    {
        $items = GalleryItem::orderBy('sort_order')->get();

        return view('pages.gallery', [
            'items' => $items,
        ]);
    }

    public function about()
    {
        return view('pages.about');
    }

    public function faq()
    {
        return view('pages.faq');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function contactSubmit(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $message = ContactMessage::create($data);

        $adminEmail = config('mail.admin_address') ?: config('mail.from.address');

        if ($adminEmail) {
            try {
                Mail::to($adminEmail)->send(new ContactMessageReceived($message));
            } catch (\Throwable $e) {
                Log::warning('Failed to send contact notification', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('contact')->with('status', 'Thanks! We received your message and will reply soon.');
    }
}
