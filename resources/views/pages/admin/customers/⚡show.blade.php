<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Customer detail')] class extends Component {
    public string $email = '';

    public function mount(): void
    {
        $this->email = (string) request()->query('email', '');

        if ($this->email === '') {
            abort(404);
        }
    }

    #[Computed]
    public function summary()
    {
        $row = Order::query()
            ->select([
                DB::raw('MAX(customer_name) as customer_name'),
                DB::raw('MAX(customer_phone) as customer_phone'),
                DB::raw('MAX(user_id) as user_id'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw("SUM(CASE WHEN payment_status = 'paid' THEN total ELSE 0 END) as total_spent"),
                DB::raw('AVG(total) as avg_total'),
                DB::raw('MAX(created_at) as last_order_at'),
                DB::raw('MIN(created_at) as first_order_at'),
            ])
            ->whereRaw('LOWER(customer_email) = ?', [strtolower($this->email)])
            ->first();

        if (! $row || ! $row->orders_count) {
            abort(404);
        }

        return $row;
    }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->whereRaw('LOWER(customer_email) = ?', [strtolower($this->email)])
            ->withCount('items')
            ->latest()
            ->paginate(10);
    }

    #[Computed]
    public function user(): ?User
    {
        if (! $this->summary->user_id) {
            return null;
        }

        return User::find($this->summary->user_id);
    }

    #[Computed]
    public function lastShippingAddress(): ?string
    {
        $latest = Order::query()
            ->whereRaw('LOWER(customer_email) = ?', [strtolower($this->email)])
            ->latest()
            ->first();

        return $latest?->shipping_address;
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:avatar :name="$this->summary->customer_name" />
                <div>
                    <flux:heading size="xl">{{ $this->summary->customer_name }}</flux:heading>
                    <flux:subheading>{{ $email }}</flux:subheading>
                </div>
            </div>
            <flux:button :href="route('admin.customers.index')" variant="ghost" icon="arrow-left" wire:navigate>
                {{ __('Back') }}
            </flux:button>
        </div>

        <div class="grid gap-4 md:grid-cols-4">
            <flux:card>
                <flux:text class="text-zinc-500">{{ __('Orders') }}</flux:text>
                <flux:heading size="lg">{{ $this->summary->orders_count }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500">{{ __('Total spent') }}</flux:text>
                <flux:heading size="lg">Rp {{ number_format((float) $this->summary->total_spent, 0, ',', '.') }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500">{{ __('Avg order') }}</flux:text>
                <flux:heading size="lg">Rp {{ number_format((float) $this->summary->avg_total, 0, ',', '.') }}</flux:heading>
            </flux:card>
            <flux:card>
                <flux:text class="text-zinc-500">{{ __('First seen') }}</flux:text>
                <flux:heading size="lg">{{ \Illuminate\Support\Carbon::parse($this->summary->first_order_at)->format('M d, Y') }}</flux:heading>
            </flux:card>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-4 lg:col-span-1">
                <flux:card>
                    <flux:heading size="lg">{{ __('Contact') }}</flux:heading>
                    <div class="mt-3 space-y-1">
                        <flux:text class="text-zinc-500">{{ __('Email') }}</flux:text>
                        <flux:text>{{ $email }}</flux:text>
                        @if ($this->summary->customer_phone)
                            <flux:text class="text-zinc-500 mt-3">{{ __('Phone') }}</flux:text>
                            <flux:text>{{ $this->summary->customer_phone }}</flux:text>
                        @endif
                    </div>
                </flux:card>

                <flux:card>
                    <flux:heading size="lg">{{ __('Account') }}</flux:heading>
                    @if ($this->user)
                        <div class="mt-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <flux:text class="text-zinc-500">{{ __('Type') }}</flux:text>
                                @if ($this->user->is_admin)
                                    <flux:badge color="emerald" size="sm">{{ __('Admin') }}</flux:badge>
                                @else
                                    <flux:badge color="emerald" size="sm">{{ __('Registered') }}</flux:badge>
                                @endif
                            </div>
                            <div class="flex items-center justify-between">
                                <flux:text class="text-zinc-500">{{ __('Joined') }}</flux:text>
                                <flux:text>{{ $this->user->created_at->format('M d, Y') }}</flux:text>
                            </div>
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="user"
                                :href="route('admin.users.edit', $this->user)"
                                wire:navigate
                                class="mt-2"
                            >
                                {{ __('Open profile') }}
                            </flux:button>
                        </div>
                    @else
                        <flux:badge color="zinc" size="sm" class="mt-3">{{ __('Guest checkout') }}</flux:badge>
                        <flux:text class="mt-2 text-zinc-500">{{ __('No account linked.') }}</flux:text>
                    @endif
                </flux:card>

                @if ($this->lastShippingAddress)
                    <flux:card>
                        <flux:heading size="lg">{{ __('Last shipping address') }}</flux:heading>
                        <flux:text class="mt-3 whitespace-pre-line text-zinc-700 dark:text-zinc-300">{{ $this->lastShippingAddress }}</flux:text>
                    </flux:card>
                @endif
            </div>

            <div class="lg:col-span-2">
                <flux:card>
                    <flux:heading size="lg">{{ __('Order history') }}</flux:heading>
                    <flux:table :paginate="$this->orders" class="mt-4">
                        <flux:table.columns>
                            <flux:table.column>{{ __('Order') }}</flux:table.column>
                            <flux:table.column>{{ __('Items') }}</flux:table.column>
                            <flux:table.column>{{ __('Total') }}</flux:table.column>
                            <flux:table.column>{{ __('Status') }}</flux:table.column>
                            <flux:table.column>{{ __('Payment') }}</flux:table.column>
                            <flux:table.column>{{ __('Placed') }}</flux:table.column>
                            <flux:table.column></flux:table.column>
                        </flux:table.columns>
                        <flux:table.rows>
                            @foreach ($this->orders as $order)
                                <flux:table.row :key="$order->id">
                                    <flux:table.cell><span class="font-mono text-sm">{{ $order->number }}</span></flux:table.cell>
                                    <flux:table.cell>{{ $order->items_count }}</flux:table.cell>
                                    <flux:table.cell>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</flux:table.cell>
                                    <flux:table.cell>
                                        @php
                                            $color = match ($order->status) {
                                                'pending' => 'amber',
                                                'paid' => 'blue',
                                                'shipped' => 'indigo',
                                                'delivered' => 'green',
                                                'cancelled' => 'red',
                                                default => 'zinc',
                                            };
                                        @endphp
                                        <flux:badge :color="$color" size="sm">{{ ucfirst($order->status) }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>
                                        @php
                                            $payColor = match ($order->payment_status) {
                                                'paid' => 'green',
                                                'pending' => 'amber',
                                                'unpaid' => 'zinc',
                                                'failed', 'expired' => 'red',
                                                'refunded' => 'purple',
                                                default => 'zinc',
                                            };
                                        @endphp
                                        <flux:badge :color="$payColor" size="sm">{{ ucfirst($order->payment_status) }}</flux:badge>
                                    </flux:table.cell>
                                    <flux:table.cell>{{ $order->created_at->format('M d, Y') }}</flux:table.cell>
                                    <flux:table.cell>
                                        <flux:button
                                            size="sm"
                                            variant="ghost"
                                            icon="eye"
                                            :href="route('admin.orders.show', $order)"
                                            wire:navigate
                                        />
                                    </flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </flux:card>
            </div>
        </div>
    </div>
</section>
