<?php

namespace App\Http\Controllers;

use App\Models\GalleryItem;
use App\Models\Product;
use App\Models\Review;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'products' => Product::orderBy('sort_order')->get(),
            'reviews' => Review::orderBy('sort_order')->get(),
            'galleryItems' => GalleryItem::orderBy('sort_order')->get(),
        ]);
    }
}
