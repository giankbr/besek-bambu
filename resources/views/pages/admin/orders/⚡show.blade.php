<?php

use App\Models\Order;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Order detail')] class extends Component {
    public Order $order;

    public string $status = 'pending';

    public function mount(Order $order): void
    {
        $this->order = $order->load('items');
        $this->status = $order->status;
    }

    public function updateStatus(): void
    {
        try {
            $this->validate([
                'status' => ['required', Rule::in(Order::STATUSES)],
            ]);

            $this->order->update(['status' => $this->status]);
            $this->order->refresh();

            Flux::toast(variant: 'success', text: __('Status updated.'));
        } catch (ValidationException $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to update'),
                text: collect($e->validator->errors()->all())->first() ?? __('Invalid status.'),
            );
            throw $e;
        } catch (\Throwable $e) {
            Flux::toast(
                variant: 'danger',
                heading: __('Failed to update'),
                text: $e->getMessage(),
            );
        }
    }
}; ?>

<section class="w-full">
    <div class="flex flex-col gap-6 p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <flux:heading size="xl">{{ $order->number }}</flux:heading>
                <flux:subheading>{{ $order->created_at->format('M d, Y · H:i') }}</flux:subheading>
            </div>
            <flux:button :href="route('admin.orders.index')" variant="ghost" icon="arrow-left" wire:navigate>
                {{ __('Back to orders') }}
            </flux:button>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <flux:card>
                    <flux:heading size="lg">{{ __('Items') }}</flux:heading>
                    <div class="mt-4 divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($order->items as $item)
                            <div class="flex items-center justify-between gap-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">{{ $item->product_icon }}</span>
                                    <div>
                                        <div class="font-medium">{{ $item->product_name }}</div>
                                        <flux:text size="sm" class="text-zinc-500">Rp {{ number_format((float) $item->price, 0, ',', '.') }} × {{ $item->quantity }}</flux:text>
                                    </div>
                                </div>
                                <div class="font-semibold">Rp {{ number_format((float) $item->line_total, 0, ',', '.') }}</div>
                            </div>
                        @endforeach
                    </div>
                    <flux:separator class="my-4" />
                    <div class="flex justify-between text-base font-semibold">
                        <span>{{ __('Total') }}</span>
                        <span>Rp {{ number_format((float) $order->total, 0, ',', '.') }}</span>
                    </div>
                </flux:card>

                <flux:card>
                    <flux:heading size="lg">{{ __('Shipping') }}</flux:heading>
                    <flux:text class="mt-2 whitespace-pre-line">{{ $order->shipping_address }}</flux:text>

                    @if ($order->shipping_courier || $order->shipping_city_name)
                        <flux:separator class="my-3" />
                        <dl class="grid grid-cols-[140px_1fr] gap-y-1 text-sm">
                            @if ($order->shipping_city_name)
                                <dt class="text-zinc-500">{{ __('City') }}</dt>
                                <dd>{{ $order->shipping_city_name }}{{ $order->shipping_province ? ', '.$order->shipping_province : '' }}</dd>
                            @endif
                            @if ($order->shipping_courier)
                                <dt class="text-zinc-500">{{ __('Courier') }}</dt>
                                <dd>{{ strtoupper($order->shipping_courier) }} {{ $order->shipping_service }}</dd>
                            @endif
                            @if ($order->shipping_etd)
                                <dt class="text-zinc-500">{{ __('Estimate') }}</dt>
                                <dd>{{ $order->shipping_etd }} {{ __('days') }}</dd>
                            @endif
                            @if ($order->shipping_weight)
                                <dt class="text-zinc-500">{{ __('Weight') }}</dt>
                                <dd>{{ number_format($order->shipping_weight) }} g</dd>
                            @endif
                            <dt class="text-zinc-500">{{ __('Cost') }}</dt>
                            <dd>{{ idr($order->shipping_cost) }}</dd>
                        </dl>
                    @elseif ($order->shipping_region)
                        <flux:separator class="my-3" />
                        <flux:text class="text-zinc-500 text-sm">{{ __('Region:') }} {{ $order->shipping_region }} — {{ idr($order->shipping_cost) }}</flux:text>
                    @endif

                    @if ($order->notes)
                        <flux:heading size="sm" class="mt-4">{{ __('Notes') }}</flux:heading>
                        <flux:text class="text-zinc-500">{{ $order->notes }}</flux:text>
                    @endif
                </flux:card>
            </div>

            <div class="space-y-6">
                <flux:card>
                    <flux:heading size="lg">{{ __('Customer') }}</flux:heading>
                    <flux:text class="mt-2">{{ $order->customer_name }}</flux:text>
                    <flux:text class="text-zinc-500">{{ $order->customer_email }}</flux:text>
                    <flux:text class="text-zinc-500">{{ $order->customer_phone }}</flux:text>
                </flux:card>

                <flux:card>
                    <flux:heading size="lg">{{ __('Status') }}</flux:heading>
                    <form wire:submit="updateStatus" class="mt-3 flex flex-col gap-3">
                        <flux:select wire:model="status">
                            @foreach (\App\Models\Order::STATUSES as $s)
                                <flux:select.option value="{{ $s }}">{{ ucfirst($s) }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:button type="submit" variant="primary">{{ __('Update status') }}</flux:button>
                    </form>
                </flux:card>

                <flux:card>
                    <flux:heading size="lg">{{ __('Payment') }}</flux:heading>
                    <div class="mt-3 space-y-2">
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
                        <div class="flex items-center justify-between">
                            <flux:text class="text-zinc-500">{{ __('Status') }}</flux:text>
                            <flux:badge :color="$payColor" size="sm">{{ ucfirst($order->payment_status) }}</flux:badge>
                        </div>
                        @if ($order->payment_method)
                            <div class="flex items-center justify-between">
                                <flux:text class="text-zinc-500">{{ __('Method') }}</flux:text>
                                <flux:text>{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</flux:text>
                            </div>
                        @endif
                        @if ($order->paid_at)
                            <div class="flex items-center justify-between">
                                <flux:text class="text-zinc-500">{{ __('Paid at') }}</flux:text>
                                <flux:text>{{ $order->paid_at->format('M d, Y · H:i') }}</flux:text>
                            </div>
                        @endif
                    </div>
                </flux:card>
            </div>
        </div>
    </div>
</section>
