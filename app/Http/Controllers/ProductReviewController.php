<?php

namespace App\Http\Controllers;

use App\Mail\ReviewReceived;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $eligibleOrder = Order::where('user_id', Auth::id())
            ->whereIn('status', ['delivered', 'paid', 'shipped'])
            ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
            ->latest()
            ->first();

        if (! $eligibleOrder) {
            return back()->with('status', 'You can only review products you have purchased.');
        }

        $exists = ProductReview::where('product_id', $product->id)
            ->where('user_id', Auth::id())
            ->exists();

        if ($exists) {
            return back()->with('status', 'You have already reviewed this product.');
        }

        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'order_id' => $eligibleOrder->id,
            'rating' => $data['rating'],
            'title' => $data['title'] ?? null,
            'body' => $data['body'],
            'is_approved' => false,
        ]);

        $recipient = trim((string) (setting('stock_alert_email') ?: store_email() ?: ''));
        if ($recipient !== '') {
            try {
                $review->load('product', 'user');
                Mail::to($recipient)->send(new ReviewReceived($review));
            } catch (\Throwable $e) {
                Log::warning('Failed to send review notification email', ['error' => $e->getMessage()]);
            }
        }

        return redirect()->route('shop.product', $product)
            ->with('status', __('Thanks! Your review is pending approval.'));
    }
}
