<?php

namespace App\Mail;

use App\Models\CartSnapshot;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedCart extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CartSnapshot $snapshot) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You left items in your cart — '.store_name(),
        );
    }

    public function content(): Content
    {
        $rawItems = (array) ($this->snapshot->items ?? []);
        $products = Product::query()
            ->whereIn('id', array_keys($rawItems))
            ->get()
            ->keyBy('id');

        $items = collect($rawItems)
            ->map(function (int $qty, int $id) use ($products) {
                $product = $products->get($id);
                if (! $product) {
                    return null;
                }

                return (object) [
                    'product' => $product,
                    'quantity' => $qty,
                    'line_total' => round($product->price * $qty, 2),
                ];
            })
            ->filter()
            ->values();

        return new Content(
            markdown: 'emails.cart.abandoned',
            with: [
                'snapshot' => $this->snapshot,
                'user' => $this->snapshot->user,
                'items' => $items,
            ],
        );
    }
}
