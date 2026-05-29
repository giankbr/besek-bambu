<?php

namespace App\Livewire\Admin;

use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\ProductReview;
use Carbon\CarbonInterface;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotificationBell extends Component
{
    /**
     * Mark unpaid/pending orders as seen (session timestamp). Only call
     * when the admin opens an order or explicitly dismisses — not on bell open.
     */
    public function markOrdersSeen(): void
    {
        session(['admin_notif_seen_at' => now()->toIso8601String()]);
        unset($this->newOrders, $this->newOrdersCount, $this->totalCount);
    }

    #[Computed]
    public function seenAt(): CarbonInterface
    {
        $value = session('admin_notif_seen_at');

        return $value ? now()->parse($value) : now()->subDay();
    }

    #[Computed]
    public function newOrders()
    {
        return Order::where('created_at', '>', $this->seenAt)
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function newOrdersCount(): int
    {
        return Order::where('created_at', '>', $this->seenAt)
            ->whereIn('payment_status', ['unpaid', 'pending'])
            ->count();
    }

    #[Computed]
    public function pendingReviews()
    {
        return ProductReview::where('is_approved', false)
            ->with('product:id,name,icon')
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function pendingReviewsCount(): int
    {
        return ProductReview::where('is_approved', false)->count();
    }

    #[Computed]
    public function unreadMessages()
    {
        return ContactMessage::where('is_read', false)
            ->latest()
            ->take(5)
            ->get();
    }

    #[Computed]
    public function unreadMessagesCount(): int
    {
        return ContactMessage::where('is_read', false)->count();
    }

    #[Computed]
    public function totalCount(): int
    {
        return $this->newOrdersCount + $this->pendingReviewsCount + $this->unreadMessagesCount;
    }

    public function render()
    {
        return view('livewire.admin.notification-bell');
    }
}
