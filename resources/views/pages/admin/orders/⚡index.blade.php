<?php

use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Orders')] class extends Component {
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $statusFilter = '';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatusFilter(): void { $this->resetPage(); }

    #[Computed]
    public function orders()
    {
        return Order::query()
            ->when($this->search !== '', function ($q) {
                $q->where(function ($w) {
                    $w->where('number', 'like', "%{$this->search}%")
                      ->orWhere('customer_name', 'like', "%{$this->search}%")
                      ->orWhere('customer_email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
            ->withCount('items')
            ->latest()
            ->paginate(15);
    }

    public function statuses(): array
    {
        return Order::STATUSES;
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div>
            <flux:heading size="xl">{{ __('Orders') }}</flux:heading>
            <flux:subheading>{{ __('All customer orders.') }}</flux:subheading>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                placeholder="{{ __('Search by number, name, email...') }}"
                class="max-w-sm"
            />
            <flux:select wire:model.live="statusFilter" placeholder="{{ __('All statuses') }}" class="max-w-xs">
                <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
                @foreach ($this->statuses() as $status)
                    <flux:select.option value="{{ $status }}">{{ ucfirst($status) }}</flux:select.option>
                @endforeach
            </flux:select>
        </div>

        <flux:table :paginate="$this->orders">
            <flux:table.columns>
                <flux:table.column>{{ __('Order') }}</flux:table.column>
                <flux:table.column>{{ __('Customer') }}</flux:table.column>
                <flux:table.column>{{ __('Items') }}</flux:table.column>
                <flux:table.column>{{ __('Total') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Placed') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->orders as $order)
                    <flux:table.row :key="$order->id">
                        <flux:table.cell>
                            <span class="font-mono text-sm">{{ $order->number }}</span>
                        </flux:table.cell>
                        <flux:table.cell>
                            <div class="font-medium">{{ $order->customer_name }}</div>
                            <flux:text size="sm" class="text-zinc-500">{{ $order->customer_email }}</flux:text>
                        </flux:table.cell>
                        <flux:table.cell>{{ $order->items_count }}</flux:table.cell>
                        <flux:table.cell>${{ number_format($order->total, 2) }}</flux:table.cell>
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
                        <flux:table.cell>{{ $order->created_at->diffForHumans() }}</flux:table.cell>
                        <flux:table.cell>
                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="eye"
                                :href="route('admin.orders.show', $order)"
                                wire:navigate
                            >
                                {{ __('View') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @empty
                    <flux:table.row>
                        <flux:table.cell colspan="7" class="text-center text-zinc-500">
                            {{ __('No orders yet.') }}
                        </flux:table.cell>
                    </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</section>
