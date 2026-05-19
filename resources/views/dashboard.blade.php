<x-layouts::app :title="__('Dashboard')">
    @php
        $totalRevenue = \App\Models\Order::where('payment_status', 'paid')->sum('total');
        $monthRevenue = \App\Models\Order::where('payment_status', 'paid')
            ->whereMonth('paid_at', now()->month)
            ->whereYear('paid_at', now()->year)
            ->sum('total');
        $orderCount = \App\Models\Order::count();
        $pendingPayment = \App\Models\Order::where('payment_status', 'pending')->count();
        $unpaidOrders = \App\Models\Order::whereIn('payment_status', ['unpaid', 'pending'])->count();
        $lowStockCount = \App\Models\Product::where('is_active', true)->where('stock', '<=', 5)->count();
        $unreadMessages = \App\Models\ContactMessage::where('is_read', false)->count();
        $pendingReviews = \App\Models\ProductReview::where('is_approved', false)->count();

        $latestOrders = \App\Models\Order::latest()->take(5)->get();
        $lowStockProducts = \App\Models\Product::where('is_active', true)
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->take(5)
            ->get();
    @endphp

    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        @if (session('status'))
            <flux:callout variant="success" icon="check-circle" :heading="session('status')" />
        @endif

        <div>
            <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
            <flux:subheading>{{ __('Overview of your store today.') }}</flux:subheading>
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <flux:card>
                <flux:text class="text-zinc-500">{{ __('Revenue (paid)') }}</flux:text>
                <div class="mt-1 text-2xl font-bold">{{ idr($totalRevenue) }}</div>
                <flux:text size="sm" class="text-zinc-500">{{ __('This month') }}: {{ idr($monthRevenue) }}</flux:text>
            </flux:card>

            <flux:card>
                <flux:text class="text-zinc-500">{{ __('Orders') }}</flux:text>
                <div class="mt-1 text-2xl font-bold">{{ number_format($orderCount) }}</div>
                <flux:text size="sm" class="text-zinc-500">{{ __('Pending payment') }}: {{ $pendingPayment }}</flux:text>
            </flux:card>

            <flux:card>
                <flux:text class="text-zinc-500">{{ __('Unpaid orders') }}</flux:text>
                <div class="mt-1 text-2xl font-bold">{{ $unpaidOrders }}</div>
                <flux:text size="sm" class="text-zinc-500">{{ __('Awaiting payment') }}</flux:text>
            </flux:card>

            <flux:card>
                <flux:text class="text-zinc-500">{{ __('Low stock') }}</flux:text>
                <div class="mt-1 text-2xl font-bold">{{ $lowStockCount }}</div>
                <flux:text size="sm" class="text-zinc-500">{{ __('≤ 5 units left') }}</flux:text>
            </flux:card>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:card>
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Latest orders') }}</flux:heading>
                    <flux:button :href="route('admin.orders.index')" size="sm" variant="ghost" icon="arrow-right" wire:navigate>
                        {{ __('All') }}
                    </flux:button>
                </div>
                <div class="mt-3 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($latestOrders as $order)
                        <a href="{{ route('admin.orders.show', $order) }}" wire:navigate class="flex items-center justify-between gap-4 py-3 hover:opacity-80">
                            <div>
                                <div class="font-mono text-sm font-medium">{{ $order->number }}</div>
                                <flux:text size="sm" class="text-zinc-500">{{ $order->customer_name }} · {{ $order->created_at->diffForHumans() }}</flux:text>
                            </div>
                            <div class="text-right">
                                <div class="font-semibold">{{ idr($order->total) }}</div>
                                @php
                                    $color = $order->isPaid() ? 'green' : ($order->payment_status === 'pending' ? 'amber' : 'zinc');
                                @endphp
                                <flux:badge :color="$color" size="sm">{{ ucfirst($order->payment_status) }}</flux:badge>
                            </div>
                        </a>
                    @empty
                        <flux:text class="text-zinc-500 text-center py-4">{{ __('No orders yet.') }}</flux:text>
                    @endforelse
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Low stock alert') }}</flux:heading>
                    <flux:button :href="route('admin.products.index')" size="sm" variant="ghost" icon="arrow-right" wire:navigate>
                        {{ __('Products') }}
                    </flux:button>
                </div>
                <div class="mt-3 divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($lowStockProducts as $product)
                        <a href="{{ route('admin.products.edit', $product) }}" wire:navigate class="flex items-center justify-between gap-4 py-3 hover:opacity-80">
                            <div class="flex items-center gap-2">
                                <span class="text-2xl">{{ $product->icon }}</span>
                                <div>
                                    <div class="font-medium">{{ $product->name }}</div>
                                    <flux:text size="sm" class="text-zinc-500">{{ idr($product->price) }}</flux:text>
                                </div>
                            </div>
                            <flux:badge :color="$product->stock === 0 ? 'red' : 'amber'" size="sm">
                                {{ $product->stock }} {{ __('left') }}
                            </flux:badge>
                        </a>
                    @empty
                        <flux:text class="text-zinc-500 text-center py-4">{{ __('All products are well stocked.') }}</flux:text>
                    @endforelse
                </div>
            </flux:card>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:card>
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Inbox') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">{{ $unreadMessages }} {{ __('unread') }}</flux:text>
                    </div>
                    <flux:button :href="route('admin.messages.index')" size="sm" variant="ghost" icon="arrow-right" wire:navigate>
                        {{ __('Open') }}
                    </flux:button>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Reviews') }}</flux:heading>
                        <flux:text size="sm" class="text-zinc-500">{{ $pendingReviews }} {{ __('pending moderation') }}</flux:text>
                    </div>
                    <flux:button :href="route('admin.reviews.index')" size="sm" variant="ghost" icon="arrow-right" wire:navigate>
                        {{ __('Open') }}
                    </flux:button>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts::app>
