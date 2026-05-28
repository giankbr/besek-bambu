<div wire:poll.30s>
    <flux:dropdown align="end">
        <button
            type="button"
            class="relative flex items-center justify-center rounded-md p-1.5 text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-800 dark:hover:bg-zinc-700 dark:hover:text-zinc-100"
            x-on:click="$wire.markOrdersSeen()"
            title="{{ __('Notifications') }}"
        >
            <flux:icon.bell class="size-5" />
            @if ($this->totalCount > 0)
                <span class="absolute -right-0.5 -top-0.5 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-semibold text-white">
                    {{ $this->totalCount > 9 ? '9+' : $this->totalCount }}
                </span>
            @endif
        </button>

        <flux:menu class="w-80">
            {{-- New Orders --}}
            <div class="px-3 py-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('New orders') }}</span>
                    @if ($this->newOrdersCount > 0)
                        <flux:badge color="amber" size="sm">{{ $this->newOrdersCount }}</flux:badge>
                    @endif
                </div>
            </div>

            @forelse ($this->newOrders as $order)
                <flux:menu.item :href="route('admin.orders.show', $order)" wire:navigate class="flex items-center justify-between gap-2">
                    <div>
                        <span class="font-mono text-sm">{{ $order->number }}</span>
                        <flux:text size="sm" class="text-zinc-500">{{ $order->customer_name }}</flux:text>
                    </div>
                    <div class="shrink-0 text-right">
                        <div class="text-sm font-medium">{{ idr($order->total) }}</div>
                        <flux:text size="sm" class="text-zinc-400">{{ $order->created_at->diffForHumans() }}</flux:text>
                    </div>
                </flux:menu.item>
            @empty
                <div class="px-3 py-2 text-sm text-zinc-400">{{ __('No new orders.') }}</div>
            @endforelse

            <flux:menu.item :href="route('admin.orders.index')" wire:navigate class="text-xs text-zinc-500">
                {{ __('View all orders →') }}
            </flux:menu.item>

            <flux:menu.separator />

            {{-- Pending Reviews --}}
            <div class="px-3 py-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('Pending reviews') }}</span>
                    @if ($this->pendingReviewsCount > 0)
                        <flux:badge color="blue" size="sm">{{ $this->pendingReviewsCount }}</flux:badge>
                    @endif
                </div>
            </div>

            @forelse ($this->pendingReviews as $review)
                <flux:menu.item :href="route('admin.reviews.index')" wire:navigate class="flex items-center gap-2">
                    <span class="text-lg">{{ $review->product?->icon }}</span>
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-medium">{{ $review->product?->name }}</div>
                        <flux:text size="sm" class="text-zinc-400">{{ str_repeat('★', $review->rating) }} · {{ $review->created_at->diffForHumans() }}</flux:text>
                    </div>
                </flux:menu.item>
            @empty
                <div class="px-3 py-2 text-sm text-zinc-400">{{ __('No pending reviews.') }}</div>
            @endforelse

            <flux:menu.item :href="route('admin.reviews.index')" wire:navigate class="text-xs text-zinc-500">
                {{ __('View all reviews →') }}
            </flux:menu.item>

            <flux:menu.separator />

            {{-- Unread Messages --}}
            <div class="px-3 py-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-zinc-500">{{ __('Unread messages') }}</span>
                    @if ($this->unreadMessagesCount > 0)
                        <flux:badge color="green" size="sm">{{ $this->unreadMessagesCount }}</flux:badge>
                    @endif
                </div>
            </div>

            @forelse ($this->unreadMessages as $msg)
                <flux:menu.item :href="route('admin.messages.index')" wire:navigate class="flex items-center gap-2">
                    <div class="min-w-0 flex-1">
                        <div class="truncate text-sm font-medium">{{ $msg->subject }}</div>
                        <flux:text size="sm" class="text-zinc-400">{{ $msg->name }} · {{ $msg->created_at->diffForHumans() }}</flux:text>
                    </div>
                </flux:menu.item>
            @empty
                <div class="px-3 py-2 text-sm text-zinc-400">{{ __('No unread messages.') }}</div>
            @endforelse

            <flux:menu.item :href="route('admin.messages.index')" wire:navigate class="text-xs text-zinc-500">
                {{ __('View all messages →') }}
            </flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</div>
