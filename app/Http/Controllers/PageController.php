<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\GalleryItem;
use Illuminate\Http\Request;

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

        ContactMessage::create($data);

        return redirect()->route('contact')->with('status', 'Thanks! We received your message and will reply soon.');
    }
}
